<?php

namespace App\Http\Controllers;

use App\Models\FinanceApplication;
use Illuminate\Http\Request;

class FinanceApplicationController extends Controller
{
    public function list(Request $request) {
        $model = new FinanceApplication();
        $params = $request->all();
        $list = $model->getLists($params);
        $data['total'] = $model->getCount($params);
        $data['list'] = $list;

        $data = array_merge($data, (array)json_decode(file_get_contents("/www/wwwlogs/limit"), true));
	$data['cityOptions'] = [
            ['label' => '厦门', 'value' => '厦门'],
            ['label' => '杭州', 'value' => '杭州'],
            ['label' => '武汉', 'value' => '武汉'],
        ];
	$data['channelOptions'] = [];
	$data['userOptions'] = [];
        return $this->apiReturn(static::OK, $data);
    }

    public function edit(Request $request) {
        $params = $request->all();
        $model = new FinanceApplication();
        $model->name = $params['name'];
        $model->mobile = $params['mobile'];
        $model->parent_id = intval($params['parent_id']);
        $model->team_id= intval($params['team_id']);
        $model->save();
        return $this->apiReturn(static::OK);
    }

    public function delete(Request $request) {
        $params = $request->all();
        $customModel = new Customer();
        $count = $customModel->where('follow_user_id', $params['id'])->count();
        if ($count > 0) {
            return $this->apiReturn(static::ERROR, [], '该账号还有'.$count.'个跟进中的客户，请转移后再删除');
        }
        $model = SystemUser::find($params['id']);
        $model->is_del = 1;
        $model->save();
        return $this->apiReturn(static::OK);
    }
}
