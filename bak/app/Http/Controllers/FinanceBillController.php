<?php

namespace App\Http\Controllers;

use App\Models\FinanceDisbursement;
use Illuminate\Http\Request;

class FinanceBillController extends Controller
{
    public function list(Request $request) {
        $model = new FinanceDisbursement();
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
        $model = new SystemUser();
        if (isset($params['id'])) {
            if (count($model->where('mobile', $params['mobile'])->where('id', '!=', $params['id'])->where('is_del', 0)->get()) > 0) {
                return $this->apiReturn(static::ERROR, [], '手机号已存在');
            }
            $model = $model->find($params['id']);
        } else {
            if (count($model->where('mobile', $params['mobile'])->where('is_del', 0)->get()) > 0) {
                return $this->apiReturn(static::ERROR, [], '手机号已存在');
            }
            $defultPwd= "crm123456";
            $model->password_salt = rand(100000, 999999);
            $model->password = md5($defultPwd. $model->password_salt);
        }
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
