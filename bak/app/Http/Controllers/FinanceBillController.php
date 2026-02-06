<?php

namespace App\Http\Controllers;

use App\Models\FinanceDisbursement;
use App\Models\FinancePaymentPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FinanceBillController extends Controller
{
    public function list(Request $request) {
        $model = new FinanceDisbursement();
        $params = $request->all();
        $list = $model->getLists($params);
        Log::info("账单详情: " . json_encode($list));
        foreach ($list as $item) {
            $item->repaid_amount = 20000;
            $item->loan_amount = 100000;
            $item->repayment_progress = 20;
            $item->loan_count = 2;
            $item->total_due_amount = 80000;
            $item->overdue_status = '无逾期';
            $item->overdue_amount = 0;
            $item->last_repayment_date = '2026-01-01';
            $item->last_repayment_amount = 110000;
        }

        $data['total'] = $model->getCount($params);
        $data['list'] = $list;
        $data = array_merge($data, (array)json_decode(file_get_contents("/www/wwwlogs/limit"), true));

        return $this->apiReturn(static::OK, $data);
    }

    public function detail(Request $request) {
        $model = new FinanceDisbursement();
        $params = $request->all();
        $list = $model->details($params['customer_name']);

        $data['total'] = $model->getCount($params);
        $data['list'] = $list;
        $data = array_merge($data, (array)json_decode(file_get_contents("/www/wwwlogs/limit"), true));

        return $this->apiReturn(static::OK, $data);
    }

    public function plan(Request $request) {
        $model = new FinanceDisbursement();
        $params = $request->all();

        $repaymentPlan = $model->getLists($params);

        $data['list'] = $repaymentPlan;
        $data['total'] = count($repaymentPlan);

        $data = array_merge($data, (array)json_decode(file_get_contents("/www/wwwlogs/limit"), true));

        return $this->apiReturn(static::OK, $data);
    }


}
