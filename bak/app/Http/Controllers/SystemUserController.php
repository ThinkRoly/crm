<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLog;
use App\Models\SystemSetting;
use App\Models\SystemRole;
use App\Models\SystemTeam;
use App\Models\SystemUser;
use App\Models\SystemUserRole;
use App\Services\AssignService;
use App\Services\RightService;
use App\Services\SelectService;
use App\Services\ToolService;
use App\Services\UserService;
use Illuminate\Http\Request;

class SystemUserController extends Controller
{
    /**
     * 用户列表
     */
    public function list(Request $request) {
        $model = new SystemUser();
        $custommodel = new CustomerLog();
        $custommodel2 = new Customer();
        $teammodel = new SystemTeam();
        $userId = $request->session()->get('user_id');
        $followUserArray = app(RightService::class)->getCustomViews($userId);
        $params = $request->all();
        if ($followUserArray == 'all') {
            $followUserList = $model->getAllUserWithDel();
            $followUserArray = collect($followUserList)->mapWithKeys(function ($item) {
                return [$item['id'] => $item['name']];
            })->toArray();
        } else {
            $params['users'] = array_keys($followUserArray);
        }
        $list = $model->getLists($params);
        $data['total'] = $model->getCount($params);
        $data['allTeamListSelect'] = app(UserService::class)->getTeamTree(); // 转成前端的select
        foreach ($list as $key => $item) {
            $config = json_decode($item['assign_rights'], true);
            $item['assign_text'] = app(AssignService::class)->genText($item['assign_rights']);
            $item['inner'] = isset($config['inner']) ? $config['inner'] : '-';
            $item['public'] = isset($config['public']) ? $config['public'] : '-';
            $item['new'] = isset($config['new']) ? $config['new'] : '-';
            $item['assign_text'] = app(AssignService::class)->genText($item['assign_rights']);
            $item['assign_text'] = app(AssignService::class)->genText($item['assign_rights']);
            $item['now'] = $custommodel->getTodayNew($item['id']);
            $item['again'] = $custommodel->getTodayAgain($item['id']);
            $item['nownow'] = $custommodel2->getUserCount($item['id']);
            $list[$key] = $item;
        }
        $data['list'] = $list;

        $data = array_merge($data, (array)json_decode(file_get_contents("/www/wwwlogs/limit"), true));
        return $this->apiReturn(static::OK, $data);
    }

    /**
     * 新增或编辑用户
     */
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

    /**
     * 锁定用户
     */
    public function lock(Request $request) {
        $params = $request->all();
        $model = SystemUser::find($params['id']);
        $model->status = $params['status'];
        $model->save();
        return $this->apiReturn(static::OK);
    }

    /**
     * 锁定用户
     */
    public function resetpwd(Request $request) {
        $params = $request->all();
        $defultPwd= "crm123456";
        $model = SystemUser::find($params['id']);
        $model->password_salt = rand(100000, 999999);
        $model->password = md5($defultPwd. $model->password_salt);
        $model->save();
        return $this->apiReturn(static::OK);
    }


    /**
     * 删除用户
     */
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
    /**
     * 获取用户信息
     */
    public function info(Request $request) {
        $userId = $request->session()->get('user_id');
        $params = $request->all();
        $model = new SystemUser();
        $teammodel = new SystemTeam();
        $allUserListSelect = $model->getAllUser($params['id']);
        $allUserListSelect = app(SelectService::class)->genSelect($allUserListSelect, 'id', 'name'); // 转成前端的select
        $allTeamListSelect = $teammodel->getAllTeam();
        $allTeamListSelect = app(SelectService::class)->genSelect($allTeamListSelect, 'id', 'name'); // 转成前端的select
        if (isset($params['id'])) {
            $model = $model->find($params['id']);
            $roleModel = new SystemRole();
            $roles = $roleModel->getRoleByUserId($params['id']);
            $model['roles'] = app(ToolService::class)->objColumn($roles, 'id');
            if (empty($model['team_id'])) {unset($model['team_id']);}
            if (empty($model['parent_id'])) {unset($model['parent_id']);}
        }
        $model['password'] = '';
        $roleModel = new SystemRole();
        $allRole = $roleModel->getRoleSelect();
        $allUser = $model->getAllUser()->toArray();
        $allUserId = array_column($allUser, 'id');
        return $this->apiReturn(static::OK, ['userInfo'=>$model, 'allUserListSelect'=>$allUserListSelect, 'roleList'=>$allRole, 'allTeamListSelect'=>$allTeamListSelect,
            'userTree' => app(UserService::class)->getUserTree(),
            'allUserId' => $allUserId
        ]);
    }

    /**
     * 获取用户角色信息
     */
    public function role(Request $request) {
        $params = $request->all();
        $userId = $params['id'];
        $roleModel = new SystemRole();
        $userroleModel = new SystemUserRole();
        $roles = $roleModel->getRoleByUserId($userId);
        $oldRoleIds = app(ToolService::class)->objColumn($roles, 'id');
        $newRoleIds = $params['roles'];
        // 该新增的权限，改移除的权限
        $delRoleIds = array_diff($oldRoleIds, $newRoleIds);
        $addRoleIds = array_diff($newRoleIds, $oldRoleIds);
        // 删除权限
        $userroleModel->delRoles($userId, $delRoleIds);
        $userroleModel->addRoles($userId, $addRoleIds);
        return $this->apiReturn(static::OK);
    }

    /**
     * 锁定用户
     */
    public function online(Request $request) {
        $userId = $request->session()->get('user_id');
        $params = $request->all();
        $model = SystemUser::find($userId);
        $model->online = $params['online'] ? 1 : 0;
        $model->save();
        return $this->apiReturn(static::OK, [], '操作成功');
    }


    /**
     * 系统设置
     */
    public function setting(Request $request) {
        $userId = $request->session()->get('user_id');
        $params = $request->all();
        $model = SystemSetting::find(1);
        if ($request->isMethod('get')) {
            if ($params['download'] == 'md5') {
                header('Content-type: application/txt');
                header('Content-Disposition: attachment; filename="md5.txt"');
                readfile('/www/wwwroot/backup/md5.txt');
                return ;
            }
            $roleModel = new SystemRole();
            $allRole = $roleModel->getRoleSelect();
            $roles = explode(",", $model->role_id);
            foreach ($roles as $key=>$r) {
                $roles[$key] = intval($r);
            }
            return $this->apiReturn(static::OK, [
                'setting'=>['ip'=>$model['ip'], 'time'=> [$model->stime.':00', $model->etime.":00"], 'role_id'=>$roles, 'gonggao'=>$model->gonggao],
                'roles' => $allRole
            ], '操作成功');
        } else {
            $model->ip = $params['ip'];
            $model->gonggao = $params['gonggao'];
            $model->stime = $params['time'][0];
            $model->etime = $params['time'][1];
            $model->role_id = implode(",", $params['role_id']);
            $model->save();
            return $this->apiReturn(static::OK, [], '操作成功');
        }
    }

    /**
     * 锁定用户
     */
    public function onoffline(Request $request) {
        $params = $request->all();
        $userId = $params['id'];
        $model = SystemUser::find($userId);
        $model->online = $params['status'] ? 1 : 0;
        $model->save();
        return $this->apiReturn(static::OK, [], '操作成功');
    }
}