<?php

namespace App\Http\Controllers;

use App\Models\FinanceApplication;
use App\Models\Channel;
use App\Models\FinanceDisbursement;
use App\Models\SystemDict;
use App\Models\SystemUser;
use Illuminate\Http\Request;

class FinanceApplicationController extends Controller
{
    public function list(Request $request) {
        $model = new FinanceApplication();
        $channelModel = new Channel();
        $userModel = new SystemUser();
        $dictModel = new SystemDict();
        $params = $request->all();

        $data['cityOptions'] = $dictModel->where('type', 1)->get()->map(function ($channel) {
            return [
                'label' => $channel->name,
                'value' => $channel->id,
            ];
        })->toArray();
        $data['channelOptions'] = $this->formatOptions($channelModel);
        $data['userOptions'] = $this->formatOptions($userModel);

        $list = $model->getLists($params);
        $data['total'] = $model->getCount($params);
        $data['list'] = $list;
        $data = array_merge($data, (array)json_decode(file_get_contents("/www/wwwlogs/limit"), true));
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
        $model = new FinanceApplication();

        // 判断是新增还是编辑
        if (isset($params['id']) && !empty($params['id'])) {
            // 编辑操作：查找现有记录并更新
            $financeApplication = FinanceApplication::find($params['id']);
            if (!$financeApplication) {
                return $this->apiReturn(static::ERROR, [], '记录不存在');
            }
        } else {
            // 新增操作：创建新记录
            $financeApplication = new FinanceApplication();
        }

        // 填充数据（可根据实际字段调整）
        $financeApplication->customer_name = $params['customer_name'] ?? '';
        $financeApplication->city = $params['city'] ?? '';
        $financeApplication->channel = $params['channel'] ?? '';
        $financeApplication->sign_date = $params['sign_date'] ?? null;
        $financeApplication->salesperson = $params['salesperson'] ?? '';
        $financeApplication->repayment_date = $params['repayment_date'] ?? null;
        $financeApplication->notarization = $params['notarization'] ?? '否';
        $financeApplication->contract_amount = $params['contract_amount'] ?? 0;
        $financeApplication->contract_rate = $params['contract_rate'] ?? '10%';
        $financeApplication->receivable_amount = $params['receivable_amount'] ?? 0;
        $financeApplication->buyout_amount = $params['buyout_amount'] ?? 0;
        $financeApplication->deposit = $params['deposit'] ?? 0;
        $financeApplication->release_amount = $params['release_amount'] ?? 0;
        $financeApplication->rebate_rate = $params['rebate_rate'] ?? '10%';
        $financeApplication->rebate_amount = $params['rebate_amount'] ?? 0;
        $financeApplication->commission_rate = $params['commission_rate'] ?? '10%';
        $financeApplication->commission_fee = $params['commission_fee'] ?? 0;
        $financeApplication->risk_control_person = $params['risk_control_person'] ?? '';
        $financeApplication->debt_settlement_amount = $params['debt_settlement_amount'] ?? 0;
        $financeApplication->department = $params['department'] ?? '';
        $financeApplication->household = $params['household'] ?? '';
        $financeApplication->education = $params['education'] ?? '';
        $financeApplication->company_full_name = $params['company_full_name'] ?? '';
        $financeApplication->company_type = $params['company_type'] ?? '';
        $financeApplication->housing_fund_base = $params['housing_fund_base'] ?? 0;
        $financeApplication->salary = $params['salary'] ?? 0;
        $financeApplication->operation_date = $params['operation_date'] ?? null;
        $financeApplication->status = $params['status'] ?? 'pending';
        $financeApplication->submit_date = $params['submit_date'] ?? null;
        $financeApplication->approver = $params['approver'] ?? '';
        $financeApplication->approval_date = $params['approval_date'] ?? null;
        $financeApplication->remark = $params['remark'] ?? '';

        // 保存数据
        try {
            $financeApplication->save();
            return $this->apiReturn(static::OK, ['id' => $financeApplication->id], '操作成功');
        } catch (\Exception $e) {
            return $this->apiReturn(static::ERROR, [], '操作失败：' . $e->getMessage());
        }
    }


    public function delete(Request $request) {
        $params = $request->all();
        $disbursementModel = new FinanceDisbursement();
        $count = $disbursementModel->where('application_id', $params['id'])->count();
        if ($count > 0) {
            return $this->apiReturn(static::ERROR, [], '该进件关联还有'.$count.'个出款，请转移后再删除');
        }
        $model = FinanceApplication::find($params['id']);
        $model->is_del = 1;
        $model->save();
        return $this->apiReturn(static::OK);
    }
}
