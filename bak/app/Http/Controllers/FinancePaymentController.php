<?php

namespace App\Http\Controllers;

use App\Models\FinancePayment;
use App\Models\FinanceApplication;
use App\Models\SystemUser;
use App\Models\Channel;
use App\Models\SystemDict;
use Illuminate\Validation\Validator;
use Illuminate\Http\Request;

class FinancePaymentController extends Controller
{
    public function list(Request $request) {
        $model = new FinancePayment();
        $applicationModel = new FinanceApplication();
        $userModel = new SystemUser();
        $channelModel = new Channel();
        $dictModel = new SystemDict();
        $params = $request->all();
        $list = $model->getLists($params);
        $data['total'] = $model->getCount($params);
        $data['list'] = $list;

        $data['cityOptions'] = $dictModel->where('type', 1)->get()->map(function ($channel) {
            return [
                'label' => $channel->name,
                'value' => $channel->name,
            ];
        })->toArray();
        $data['channelOptions'] = $this->formatOptions($channelModel);

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
                'value' => $channel->name,
            ];
        })->toArray();
        return $this->apiReturn(static::OK, $data);
    }

    private function formatOptions($model, $labelField = 'name', $valueField = 'id')
    {
        return $model->get()->map(function ($item) use ($labelField, $valueField) {
            return [
                'label' => $item->$labelField,
                'value' => $item->$valueField,
            ];
        })->toArray();
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

    public function delete(Request $request) {
        $params = $request->all();
        $model = FinancePayment::find($params['id']);
        $model->is_del = 1;
        $model->save();
        return $this->apiReturn(static::OK);
    }
}
