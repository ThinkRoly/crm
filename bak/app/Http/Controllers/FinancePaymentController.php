<?php

namespace App\Http\Controllers;

use App\Models\FinancePayment;
use App\Models\FinanceApplication;
use App\Models\SystemUser;
use Illuminate\Validation\Validator;
use Illuminate\Http\Request;

class FinancePaymentController extends Controller
{
    public function list(Request $request) {
        $model = new FinancePayment();
        $applicationModel = new FinanceApplication();
        $userModel = new SystemUser();
        $params = $request->all();
        $list = $model->getLists($params);
        $data['total'] = $model->getCount($params);
        $data['list'] = $list;

        $data = array_merge($data, (array)json_decode(file_get_contents("/www/wwwlogs/limit"), true));
        $data['applicationOptions'] = $applicationModel->where('is_del', 0)->get()->map(function ($application) {
            return [
                'value' => $application->id,
                'label' => $application->customer_name,
                'customer_name' => $application->customer_name,
                'city' => $application->city,
                'channel' => $application->channel,
            ];
        })->toArray();

        $data['userOptions'] = $userModel->get()->map(function ($channel) {
            return [
                'label' => $channel->name,
                'value' => $channel->tid,
            ];
        })->toArray();
        return $this->apiReturn(static::OK, $data);
    }

    public function edit(Request $request) {
        $params = $request->all();
        $rules = [
            'id'        => 'nullable|integer|exists:finance_payments,id',
            'customer_name' => 'required|string|max:255',
            'amount'    => 'required|integer|min:0',
            'received_amount' => 'required|integer|min:0',
            'received_date' => 'required|date',
            'status'    => 'required|string',
            'remark'    => 'nullable|string|max:1000'
        ];
	    $messages = [
            'id.exists' => '待编辑的支付记录不存在',
            'received_date.date' => '到账日期格式错误',
            '*.min'     => '数值不能为负数',
            '*.required'=> '该字段为必填项'
        ];
        $validator = \Validator::make($params, $rules, $messages);
        if ($validator->fails()) {
                return $this->apiReturn(422, [], $validator->errors()->first());
        }
        $validated = $validator->validated();
        $model = $validated['id'] ?? false ? FinancePayment::findOrFail($validated['id']) : new FinancePayment();


        $model->customer_name = $validated['customer_name'];
        $model->amount = $validated['amount'];
        $model->received_amount = $validated['received_amount'];
        $model->received_date = $validated['received_date'];
        $model->status = $validated['status'];
        $model->remark = $validated['remark'] ?? '';

        $model->save();

        return $this->apiReturn(static::OK, ['data' => $model]);
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
            $plan = $planModel->processRepayment(
                $params['plan_id'],
                $params['repayment_amount'],
                $today,
            );

            return $this->apiReturn(static::OK, [
                'message' => '还款成功',
                'plan' => $plan
            ]);
        } catch (\Exception $e) {
            return $this->apiReturn(500, [], $e->getMessage());
        }
    }

    public function delete(Request $request) {
        $params = $request->all();
        $model = FinancePayment::find($params['id']);
        $model->is_del = 1;
        $model->save();
        return $this->apiReturn(static::OK);
    }
}
