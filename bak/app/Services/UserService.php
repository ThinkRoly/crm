<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerLog;
use App\Models\CustomerRuleConfig;
use App\Models\SystemTeam;
use App\Models\SystemUser;
use App\Models\SystemUserRole;

class UserService
{
    /**
     * 获取有新数据分配权限的所有用户和剩余的分配次数
     */
    public function getAllAssignUser($flag) {
        $model = new SystemUser();
        $logmodel = new CustomerLog();
        $users = $model->where('status', 1)->where('is_del', 0)->where('online', 1)->get();
        $retUser = [];
        $sort1 = [];
        $sort2 = [];
        foreach ($users as $user) {
            $config = json_decode($user['assign_rights'], true);
            if ($config["new"] > 0) {
		if($flag){
                    $count = $logmodel->getCountByUserIdAndTimeIgnore($user['id'], CustomerLog::TYPE_ASSIGN_NEW, date('Y-m-d 00:00:00'), date('Y-m-d 00:00:00', strtotime('+1 day')))[0];
		}else{
                    $count = $logmodel->getCountByUserIdAndTimeWithSource($user['id'], CustomerLog::TYPE_ASSIGN_NEW, date('Y-m-d 00:00:00'), date('Y-m-d 00:00:00', strtotime('+1 day')))[0];
		}
                $leftCount = $config["new"] - $count->cnt;
                $assignLeftCount = $this->getAssginLeftTime($user['id']);
                $leftCount = min($leftCount, $assignLeftCount);
                $sort1[] = intval($count->id);
                #$sort2[] = $leftCount;
                $sort2[] = $count->cnt;
                #$retUser[$user['id'].'-UID'] = $leftCount; 
                $retUser[$user['id'].'-UID'] = $count->cnt; 
            }
        }
        #array_multisort($sort1, SORT_ASC, $sort2, SORT_DESC, $retUser);
        array_multisort($sort2, SORT_ASC, $sort1, SORT_ASC, $retUser);
        return $retUser;
    }

    /**
     * 获取有认领剩余次数
     */
    public function getGetLeftTime($userId) {
        $model = new SystemUser();
        $logmodel = new CustomerLog();
        $user = $model->find($userId);
        $config = json_decode($user['assign_rights'], true);
        $leftCount = 0;
        if ($config["public"] > 0) {
            $count = $logmodel->getCountByUserIdAndTime($user['id'], CustomerLog::TYPE_GET, date('Y-m-d 00:00:00'), date('Y-m-d 00:00:00', strtotime('+1 day')))[0];
            $leftCount = $config["public"] - $count->cnt;
        }
        return $leftCount;
    }

       /**
     * 获取权限树
     * 
     * @param userId 信贷员id
     */
    public function getUserTree($userId = 0, $userIds = [])
    {
        $model = new SystemUser();
        $users = $model->getAllUserByParentId($userId);
        $list = [];
        foreach ($users as $user) {
            if (in_array($user->id, $userIds)) {
                continue;
            }
            $userIds[] = $user->id;
            $item = [
                'title' => $user->name,
                'key'  => $user->id,
            ];
            $children = $this->getUserTree($user->id, $userIds);
            if ($children) {
                $item['children'] = $children;
            }
            $list[] = $item;
        }
        return $list;
    }

    
       /**
     * 获取权限树
     * 
     * @param userId 信贷员id
     */
    public function getTeamTree($teamId = 0, $withInfo = false)
    {
        $model = new SystemTeam();
        $usermodel = new SystemUser();
        $allUserListSelect = $usermodel->getAllUserWithDel();
        $allUserListSelect = app(SelectService::class)->genKv($allUserListSelect, 'id', 'name'); // 转成前端的select
        //$teams= $model->getAllTeamById($teamId);
        $teams= $model->getAllTeam();
        $list = [];
        foreach ($teams as $team) {
            $manager = "";
            $usercount = 0;
            if ($withInfo) {
                $manager = $allUserListSelect[$team->manager_id];
                $usercount = $usermodel->getUserCountByTeamid($team->id);
            }
            $item = [
                'id' => $team->id,
                'name' => $team->name,
                'key' => $team->id,
                'title' => $team->name,
                'create_time' => $team->create_time,
                'manager' => $manager,
                'usercount' => $usercount,
                'parent_id' => $team->parent_id,
            ];
            $list[] = $item;
        }
        return $this->buildHierarchy($list, $teamId);
    }

    function buildHierarchy($items, $parentId = 0, $level = 0)
    {
        $result = [];
        // 嵌套超过5次就拉倒
        if ($level > 5) {
            return $result;
        }
        foreach ($items as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = $this->buildHierarchy($items, $item['id'], $level ++);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * 获取所有下级团队
     * 
     * @param userId 信贷员id
     */
    public function getTeams($teamId = 0)
    {
        $model = new SystemTeam();
        $teams = $model->getAllTeam();
        if (empty($teams)) {
            return [];
        } else {
            return $this->getChildIds($teams->toArray(), $teamId);
        }
    }

    function getChildIds($items, $parentId, $level = 0)
    {
        $result = [$parentId];
        if ($level > 5) {
            return [];
        }
        foreach ($items as $item) {
            if ($item['parent_id'] == $parentId) {
                $result[] = $item['id'];
                $result = array_merge($result, $this->getChildIds($items, $item['id']));
            }
        }
        return $result;
    }

    
    /**
     * 获取剩余用户数量
     */
    public function getAssginLeftTime($userId) {
        $model = new SystemUserRole();
        $roles = $model->getRoleByUserId($userId);
        $rolesIds = array_column($roles, 'role_id');

        if (in_array(SystemUserRole::ROLE_ADMIN, $rolesIds)) {
            return 100000;
        }
        $configModel = new CustomerRuleConfig();
        if (in_array(SystemUserRole::ROLE_QUZHANG, $rolesIds)) {
            $rule = $configModel->find(15);
        } else if (in_array(SystemUserRole::ROLE_ZHUGUAN, $rolesIds)) {
            $rule = $configModel->find(14);
        } else if (in_array(SystemUserRole::ROLE_JINGLI , $rolesIds)) {
            $rule = $configModel->find(12);
        } else {
            return 100000;
        }
        $status = $rule->status;
        if ($status == 1) {
            $config = json_decode($rule->config, true);
            $num = $config['num'];
            $cnt = Customer::where('follow_user_id', $userId)->where('follow_status', '!=', 5)->count();
            return max($num - $cnt, 0);
        }
        return 100000;
    }
}
