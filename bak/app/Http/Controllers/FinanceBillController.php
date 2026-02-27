<?php

namespace App\Http\Controllers;

use App\Models\FinanceDisbursement;
use App\Models\FinancePaymentPlan;
use App\Models\FinancePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FinanceBillController extends Controller
{
    public function list(Request $request) {
        $model = new FinanceDisbursement();
        $paymentPlanModel = new FinancePaymentPlan();
        $params = $request->all();
        $list = $model->getBills($params);
        foreach ($list as $item) {
            $repaidAmount = $paymentPlanModel->where('customer_name', $item->customer_name)
                ->where('status', 'completed')
                ->sum('paid_amount');
            $item->repaid_amount = $repaidAmount ?: 0;

            $totalPeriods = $paymentPlanModel->where('customer_name', $item->customer_name)->count();
            $completedPeriods = $paymentPlanModel->where('customer_name', $item->customer_name)
                ->where('status', 'completed')
                ->count();

            if ($totalPeriods > 0) {
                $item->repayment_progress = round(($completedPeriods / $totalPeriods) * 100, 2);
            } else {
                $item->repayment_progress = 0;
            }

            $totalDueAmount = $paymentPlanModel->where('customer_name', $item->customer_name)
                ->sum('due_amount');
            $item->total_due_amount = $totalDueAmount ?: 0;

            $overduePlans = $paymentPlanModel->where('customer_name', $item->customer_name)
                ->where('status', 'overdue')
                ->get();

            $item->overdue_amount = $overduePlans->sum('due_amount') - $overduePlans->sum('paid_amount');
            $item->overdue_status = $item->overdue_amount > 0 ? '有逾期' : '无逾期';

            // 获取最后还款记录
            $lastRepayment = $paymentPlanModel->where('customer_name', $item->customer_name)
                ->where('paid_date', '!=', null)
                ->orderBy('paid_date', 'desc')
                ->first();

            if ($lastRepayment) {
                $item->last_repayment_date = $lastRepayment->paid_date;
                $item->last_repayment_amount = $lastRepayment->paid_amount;
            } else {
                $item->last_repayment_date = null;
                $item->last_repayment_amount = 0;
            }
        }

        $data['total'] = $model->getCount($params);
        $data['list'] = $list;
        $data = array_merge($data, (array)json_decode(file_get_contents("/www/wwwlogs/limit"), true));

        return $this->apiReturn(static::OK, $data);
    }

    public function detail(Request $request) {
        $model = new FinanceDisbursement();
        $paymentPlanModel = new FinancePaymentPlan();
        $params = $request->all();

        $customerName = $params['customer_name'] ?? '';
        if (empty($customerName)) {
            return $this->apiReturn(static::ERROR, [], '客户名称不能为空');
        }
        $disbursements = $model->where('customer_name', $customerName)->get();
        if ($disbursements->isEmpty()) {
            return $this->apiReturn(static::ERROR, [], '未找到该客户的放款记录');
        }
        $resultList = [];

        foreach ($disbursements as $disbursement) {
            // 获取该放款对应的所有还款计划
            $repaymentPlans = $paymentPlanModel->where('disbursement_id', $disbursement->id)
                ->orderBy('period', 'asc')
                ->get();

            if ($repaymentPlans->isEmpty()) {
                continue; // 跳过没有还款计划的放款
            }
            // 为每个disbursement_id生成汇总数据
            $planSummary = [
                'id' => $disbursement->id,
                'customer_name' => $disbursement->customer_name,
                'disbursement_amount' => $disbursement->disbursement_amount,
                'channel' => $disbursement->channel,
                'city' => $disbursement->city,
                'period' => $repaymentPlans->count(),
                'paid_period' => $repaymentPlans->where('status', 'completed')->count(),
                'total_period' => $repaymentPlans->count(),
                'remaining_amount' => $repaymentPlans->sum('due_amount') - $repaymentPlans->sum('paid_amount'),
                'paid_amount' => $repaymentPlans->sum('paid_amount'),
                'next_repayment_date' => $repaymentPlans->where('status', 'pending')->first()->due_date ?? null,
                'status' => $repaymentPlans->where('status', 'overdue')->count() > 0 ? '逾期' : '正常'
            ];

            $resultList[] = $planSummary;
        }

        if (empty($resultList)) {
            return $this->apiReturn(static::ERROR, [], '该客户暂无有效的还款计划');
        }

        $data = [
            'list' => $resultList,
            'total' => count($resultList)
        ];
        $data = array_merge($data, (array)json_decode(file_get_contents("/www/wwwlogs/limit"), true));

        return $this->apiReturn(static::OK, $data);
    }

    public function plan(Request $request) {
        $model = new FinancePaymentPlan();
        $params = $request->all();
        $repaymentPlan = $model->getLists($params);

        $data['list'] = $repaymentPlan;
        $data['total'] = $model->getCount($params);

        $data = array_merge($data, (array)json_decode(file_get_contents("/www/wwwlogs/limit"), true));

        return $this->apiReturn(static::OK, $data);
    }

    public function repay(Request $request) {
        $params = $request->all();
        $rules = [
            'plan_id' => 'required|integer|exists:finance_payment_plan,id',
        ];

        $messages = [
            'plan_id.required' => '账单ID不能为空',
            'plan_id.exists' => '账单不存在',
            'repayment_amount.required' => '还款金额不能为空',
            'repayment_amount.min' => '还款金额必须大于0',
            'repayment_date.required' => '还款日期不能为空',
            'repayment_date.date' => '还款日期格式错误'
        ];

        $validator = \Validator::make($params, $rules, $messages);
        if ($validator->fails()) {
            return $this->apiReturn(422, [], $validator->errors()->first());
        }
        $today = date('Y-m-d');
        try {
            $planModel = new FinancePaymentPlan();
            $paymentModel = new FinancePayment();
            $disbursementModel = new FinanceDisbursement();

            $plan = $planModel->find($params['plan_id']);
            if (!$plan) {
                return $this->apiReturn(static::ERROR, [], '账单不存在');
            }
            if ($plan->status === 'completed') {
                return $this->apiReturn(static::ERROR, [], '账单已还清');
            }
            DB::beginTransaction();
            $disbursement = $disbursementModel->find($plan->disbursement_id);
            $payment = new FinancePayment([
                'disbursement_id' => $disbursement->id,
                'customer_name' => $disbursement->customer_name,
                'channel' => $disbursement->channel,
                'city' => $disbursement->city,
                'sign_date' => $disbursement->sign_date,
                'repayment_amount' => $params['repayment_amount'],
                'repayment_date' => $today,
                'repayment_type' => $disbursement->disbursement_type,
                'channel_point' => $disbursement->channel_point,
                'channel_amount' => $disbursement->channel_amount,
            ]);

            $plan = $planModel->processRepayment(
                $params['plan_id'],
                $params['repayment_amount'],
                $today,
            );
            $payment->save();
            DB::commit();
            return $this->apiReturn(static::OK, [
                'message' => '还款成功',
                'plan' => $plan
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->apiReturn(static::ERROR, [], $e->getMessage());
        }
    }


}
