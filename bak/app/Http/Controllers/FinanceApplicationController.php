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
        $model->save();
        return $this->apiReturn(static::OK);
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
