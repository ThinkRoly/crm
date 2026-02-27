<?php

namespace App\Http\Controllers;

use App\Models\FinanceDisbursement;
use App\Models\FinancePaymentPlan;
use App\Models\FinanceApplication;
use App\Models\Channel;
use App\Models\SystemDict;
use App\Models\SystemUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FinanceDisbursementController extends Controller
{
    public function list(Request $request) {
        $model = new FinanceDisbursement();
        $applicationModel = new FinanceApplication();
        $channelModel = new Channel();
        $dictModel = new SystemDict();

        $userModel = new SystemUser();
        $params = $request->all();
        $list = $model->getLists($params);
        $data['total'] = $model->getCount($params);
        $data['list'] = $list;

        $data = array_merge($data, (array)json_decode(file_get_contents("/www/wwwlogs/limit"), true));

        $cityData = $dictModel->where('type', 1)->get()->pluck('name', 'tid')->toArray();
        $channelData = $channelModel->get()->pluck('name', 'id')->toArray();

        $data['applicationOptions'] = $applicationModel->where('is_del', 0)->get()->map(function ($application) use ($cityData, $channelData) {
            return [
                'value' => $application->id,
                'label' => ($application->customer_name ?? '') . '-' . $application->id,
                'customer_name' => $application->customer_name,
                'city' => $cityData[$application->city] ?? '未知城市',
                'channel' => $channelData[$application->channel] ?? '未知渠道',
            ];
        })->toArray();

        $data['accountOptions'] = [
            ['label' => '施', 'value' => '施'],
            ['label' => '马', 'value' => '马'],
            ['label' => '韦', 'value' => '韦'],
            ['label' => '郑', 'value' => '郑'],
            ['label' => '通道', 'value' => '通道'],
        ];

        $data['userOptions'] = $userModel->get()->map(function ($channel) {
            return [
                'label' => $channel->name,
                'value' => $channel->id,
            ];
        })->toArray();

        return $this->apiReturn(static::OK, $data);
    }

    public function add(Request $request) {
//         $validator = Validator::make($request->all(), [
//             'application_id' => 'required|exists:finance_application,id',
//             'customer_name' => 'required|string|max:100',
//             'channel' => 'required|string|max:50',
//             'city' => 'required|string|max:50',
//             'sign_date' => 'required|date',
//             'disbursement_amount' => 'required|numeric|min:0.01',
//             'disbursement_type' => 'required|string|max:50',
//             'period' => 'required|integer|min:1',
//             'account' => 'required|string|max:50',
//             'interest_rate' => 'required|numeric|min:0|max:100',
//             'monthly_repayment_amount' => 'required|numeric|min:0',
//         ]);
//
//         if ($validator->fails()) {
//             return $this->apiReturn(static::ERROR, [], $validator->errors()->first());
//         }

        $data = $request->only([
            'application_id', 'customer_name', 'channel', 'city', 'sign_date',
            'disbursement_amount', 'disbursement_type', 'period', 'disbursement_date',
            'account', 'interest_rate', 'monthly_repayment_amount',
            'channel_point', 'channel_amount', 'salesperson', 'remark'
        ]);


        return \DB::transaction(function () use ($request) {
            $data = $request->only([
                'application_id', 'customer_name', 'channel', 'city', 'sign_date',
                'disbursement_amount', 'disbursement_type', 'period', 'disbursement_date',
                'account', 'interest_rate', 'monthly_repayment_amount',
                'channel_point', 'channel_amount', 'salesperson', 'remark'
            ]);

            $disbursement = FinanceDisbursement::create($data);

            $this->generateRepaymentPlans($disbursement);

            return $this->apiReturn(static::OK, ['id' => $disbursement->id]);
        });
    }

    public function update(Request $request, $id)
    {
        $disbursement = FinanceDisbursement::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:100',
            'channel' => 'required|string|max:50',
            'city' => 'required|string|max:50',
            'sign_date' => 'required|date',
            'disbursement_amount' => 'required|numeric|min:0.01',
            'disbursement_type' => 'required|string|max:50',
            'period' => 'required|integer|min:1',
            'account' => 'required|string|max:50',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'monthly_repayment_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->apiReturn(static::ERROR, [], $validator->errors()->first());
        }

        $disbursement->update($request->only([
            'customer_name', 'channel', 'city', 'sign_date',
            'disbursement_amount', 'disbursement_type', 'period', 'disbursement_date',
            'account', 'interest_rate', 'monthly_repayment_amount',
            'channel_point', 'channel_amount', 'salesperson', 'remark'
        ]));

        if (
                $disbursement->period !== $oldPeriod ||
                $disbursement->monthly_repayment_amount !== $oldMonthly
        ) {
            $this->generateRepaymentPlans($disbursement);
        }

        return $this->apiReturn(static::OK);
    }


    private function generateRepaymentPlans(FinanceDisbursement $disbursement)
    {
        // 删除旧计划（确保一致性）
        \DB::table('finance_payment_plan')
            ->where('disbursement_id', $disbursement->id)
            ->delete();

        $period = $disbursement->period;
        $totalAmount = $disbursement->disbursement_amount;
        $monthlyAmount = $disbursement->monthly_repayment_amount;
        $signDate = \Carbon\Carbon::parse($disbursement->sign_date);

        $plans = [];
        $remaining = $totalAmount;

        for ($i = 1; $i <= $period; $i++) {
            $dueDate = $signDate->copy()->addMonths($i);
            $dueAmount = $monthlyAmount;

            $plans[] = [
                'disbursement_id' => $disbursement->id,
                'customer_name' => $disbursement->customer_name,
                'sign_date' => $disbursement->sign_date,
                'period' => $i,
                'total_period' => $period,
                'paid_amount' => 0.00,
                'due_amount' => $dueAmount,
                'due_date' => $dueDate->toDateString(),
                'status' => 'pending',
            ];

            $remaining -= $dueAmount;
        }

        // 批量插入
        \DB::table('finance_payment_plan')->insert($plans);
    }

    public function delete(Request $request) {
        $params = $request->all();
        FinanceDisbursement::findOrFail($params['id'])->delete();
        FinancePaymentPlan::where('disbursement_id', $params['id'])->delete();
        return $this->apiReturn(static::OK);
    }
}
