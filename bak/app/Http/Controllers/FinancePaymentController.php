<?php

namespace App\Http\Controllers;

use App\Models\FinancePayment;
use Illuminate\Validation\Validator;
use Illuminate\Http\Request;

class FinancePaymentController extends Controller
{
    public function list(Request $request) {
        $model = new FinancePayment();
        $params = $request->all();
        $list = $model->getLists($params);
        $data['total'] = $model->getCount($params);
        $data['list'] = $list;

        $data = array_merge($data, (array)json_decode(file_get_contents("/www/wwwlogs/limit"), true));
	$data['cityOptions'] = [];
	$data['channelOptions'] = [];
	$data['userOptions'] = [];
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

    public function delete(Request $request) {
        $params = $request->all();
        $model = FinancePayment::find($params['id']);
        $model->is_del = 1;
        $model->save();
        return $this->apiReturn(static::OK);
    }
}
