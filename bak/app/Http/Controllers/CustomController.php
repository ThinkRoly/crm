<?php

namespace App\Http\Controllers;

use App\Models\Approve;
use App\Models\ApproveDetail;
use App\Models\Constants;
use App\Models\Customer;
use App\Models\CustomerBack;
use App\Models\CustomerCallLog;
use App\Models\CustomerDengjiLog;
use App\Models\CustomerLog;
use App\Models\CustomerRemarkLog;
use App\Models\CustomerRuleConfig;
use App\Models\Excel\CustomerModel;
use App\Models\Notice;
use App\Models\Product;
use App\Models\SystemDict;
use App\Models\SystemLog;
use App\Models\SystemRight;
use App\Models\SystemRole;
use App\Models\SystemTeam;
use App\Models\SystemUser;
use App\Models\SystemUserRole;
use App\Providers\AppServiceProvider;
use App\Services\ApproveService;
use App\Services\CustomerService;
use App\Services\RightService;
use App\Services\ToolService;
use App\Services\SelectService;
use App\Services\UserService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class CustomController extends Controller
{
    /**
     * 客户列表
     */
    public function list(Request $request)
    {
        $userId = $request->session()->get('user_id');
        $data = [];
        $dictModel = new SystemDict();
        $userModel = new SystemUser();
        $customModel = new Customer();
        $customerRemarkLog = new CustomerRemarkLog();
        
        $teammodel = new SystemTeam();
        $allTeamListSelect = $teammodel->getAllTeam();
        $followTeamArray = collect($allTeamListSelect)->mapWithKeys(function ($item) {
            return [$item['id'] => $item['name']];
        })->toArray();
	//$userInfo = SystemUser::find($userId);
        // 获取客户维度的码表
        $customGourpDict = $dictModel->getListByGroup($dictModel::GROUP_CUSTOM); 
        $data['cityList'] = app(SelectService::class)->genSelectByKV($customGourpDict[$dictModel::TYPE_CITY]); // 城市
        $data['followStatusList'] = app(SelectService::class)->genSelectByKV($customGourpDict[$dictModel::TYPE_FOLLOW]); // 跟进状态
        $stars = $customGourpDict[$dictModel::TYPE_STAR];
        unset($stars[-1]);
        $data['starList'] = app(SelectService::class)->genSelectByKV($stars); // 星级
        $data['starList'][] = ['value'=>'-1', 'label'=>'0星'];
        $data['starList'][] = ['value'=>'6', 'label'=>'2星以上'];
        $data['starList'][] = ['value'=>'9', 'label'=>'2星\3星'];
        $data['starList'][] = ['value'=>'10', 'label'=>'4星\5星'];
        $data['userFromList'] = app(SelectService::class)->genSelectByKV($customGourpDict[$dictModel::TYPE_USER_FROM]); // 用户来源
        $data['sourceList'] = app(SelectService::class)->genSelectByKV($customGourpDict[$dictModel::TYPE_SOURCE]); // 渠道来源
        $data['zizhiList'] = app(SelectService::class)->genSelectByKV($customGourpDict[$dictModel::TYPE_QUALIFICATION]); // 资质说明
        $data['teamList'] = app(UserService::class)->getTeamTree(); // 转成前端的select

        // 筛选项
        $params = $request->all();
        $export = $params['export'];
        $data['master'] = 0;
        $followUserArray = app(RightService::class)->getCustomViews($userId);
        if ($followUserArray == 'all') {
            $followUserList = $userModel->getAllUser();
            $followUserArray = collect($followUserList)->mapWithKeys(function ($item) {
                return [$item['id'] => $item['name']];
            })->toArray();
            $data['master'] = 1;
        } else {
            if (empty($params['followUserId']) && empty($params['team_id'])) {
                $params['followUserId'] = $userId;
            } else {
                $params['users'] = array_keys($followUserArray);
            }
        }
        $data['followUserList'] = app(SelectService::class)->genSelectByKV($followUserArray); // 转成前端的select

        if ($params['type'] == 'MyCustomerList') {
            $params['followUserId'] = $userId;
        }

        // 加工显示项目
        $list = $customModel->getLists($params);
        $customIds = [];
        foreach ($list as $key => $item) {
            $customIds[] = $item['id'];
            $item['notFollowTime'] = app(CustomerService::class)->getNotFollowTime($item);
            $item['mobile'] = app(CustomerService::class)->decrypt($item['mobile_jiami']);
            $item['apply_time'] = empty($item['apply_time']) ? '-' : date('Y-m-d H:i:s', $item['apply_time']);
            $item['follow_time'] = empty($item['follow_time']) ? '-' : date('Y-m-d H:i:s', $item['follow_time']);
            $item['assign_time'] = empty($item['assign_time']) ? '-' : date('Y-m-d H:i:s', $item['assign_time']);
            $item['zizhi'] = app(CustomerService::class)->genZizhi($item);
            if ($item['star'] == -1) {
                $item['starcolor'] = 'lime';
            } else {
                $item['starcolor'] = Constants::TAG_COLOR[$item['star']] ?? '';
            }
            $item['star'] = $customGourpDict[$dictModel::TYPE_STAR][$item['star']] ?? '';
            $item['fstatuscolor'] = Constants::TAG_COLOR[$item['follow_status']];
            $item['follow_status'] = $customGourpDict[$dictModel::TYPE_FOLLOW][$item['follow_status']] ?? '-';
            $item['user_from'] = $customGourpDict[$dictModel::TYPE_USER_FROM][$item['user_from']];
            $item['source_id'] = $item['source'];
            $item['source'] = $customGourpDict[$dictModel::TYPE_SOURCE][$item['source']];
            $item['follow_user'] = $followUserArray[$item['follow_user_id']];
            $item['age']  = empty($item['age']) ? '' : $item['age'];
            $item['amount']  = $item['amount'] == 0 ? '' : intval($item['amount']);
            $item['city'] = $customGourpDict[$dictModel::TYPE_CITY][$item['city']] ?? '-';
            $remarks = $customerRemarkLog->getLogListByCustomerId($item['id'], $export ? 100 : 100);
            $item['remark'] = implode($export ? "\n":"\n", collect($remarks)->map(function($item) use ($followUserArray, $export, $index) {
                return $item->remark . ($export ? "[".$item->create_time.":".$followUserArray[$item['user_id']]."]" : "");
            })->toArray());
            $index = count($remarks);
            for ($i = 0; $i < ($index < 3 ? $index : 3); $i++) {
                $item['subremark'] .= ($index - $i) . "、 " . ($remarks[$i] -> remark) ;
                if ($i < 2) {
                    $item['subremark'] .= '<br/>';
                }
            }
            $item['subremark'] = mb_substr($item['subremark'], 0, 100);
            $list[$key] = $item;
        }
        $data['list'] = $list;
        $data['customIds'] = base64_encode(implode('-', $customIds));
        $data['total'] = $customModel->getCount($params);
        if ($export == 1) {
            app(ToolService::class)->csv(
                "我的客户",
                [
                    "id" =>       "id",
                    "name" =>       "姓名",
                    "mobile"  =>   "手机号",
                    "star" =>   "星级",
                    "source" =>         "渠道",
                    "follow_status" =>       "跟进状态",
                    "remark" =>        "跟进备注"  ,
                    "follow_time" =>    "最新跟进时间" ,
                ],
                $list
            );
            return;
        }
        return $this->apiReturn(static::OK, $data);
    }


    /**
     * 获取用户信息
     */
    public function info(Request $request)
    {
        $userId = $request->session()->get('user_id');
        $color = ['', 'green', 'blue', 'purple', 'pinkpurple', 'red'];
        $params = $request->all();

        $dictModel = new SystemDict();
        $customModel = new Customer();

        $roleModel = new SystemRole();
        $productModel  = new Product();
        $quanzheng = $roleModel->getUserByRoleId(2);
        $quanzhengUserArray = collect($quanzheng)->mapWithKeys(function ($item) {
            return [$item->id => $item->name];
        })->toArray();
        $products = $productModel->getAllProduct();
        $productArray = collect($products)->mapWithKeys(function ($item) {
            return [$item->id => $item->name];
        })->toArray();
        
        // 获取客户维度的码表
        $customGourpDict = $dictModel->getListByGroup($dictModel::GROUP_CUSTOM); 
        $data['cityList'] = app(SelectService::class)->genSelectByKV($customGourpDict[$dictModel::TYPE_CITY]); // 城市
        $data['followStatusList'] = app(SelectService::class)->genSelectByKV($customGourpDict[$dictModel::TYPE_FOLLOW]); // 跟进状态
        $data['starList'] = app(SelectService::class)->genSelectByKV($customGourpDict[$dictModel::TYPE_STAR]); // 星级
        $data['userFromList'] = app(SelectService::class)->genSelectByKV($customGourpDict[$dictModel::TYPE_USER_FROM]); // 用户来源
        $data['sourceList'] = app(SelectService::class)->genSelectByKV($customGourpDict[$dictModel::TYPE_SOURCE]); // 渠道来源
        $data['zizhiList'] = app(SelectService::class)->genSelectByKV($customGourpDict[$dictModel::TYPE_QUALIFICATION]); // 资质说明
        $data['workList'] = app(SelectService::class)->genSelectByKV($customGourpDict[$dictModel::TYPE_WORK]); // 工作类型
        unset($customGourpDict[$dictModel::TYPE_NOTICE][5]);
        $data['noticesList'] = app(SelectService::class)->genSelectByKV($customGourpDict[$dictModel::TYPE_NOTICE]); // 通知类型
        $data['quanzhengList'] = app(SelectService::class)->genSelectByKV($quanzhengUserArray); //权证 
        $data['productList'] = app(SelectService::class)->genSelectByKV($productArray); //权证 
        $logList = [];

        $model = new SystemUserRole();
        $roles = $model->getRoleByUserId($userId);
        $rolesIds = array_column($roles, 'role_id');

        $isAdmin = false;
        if (in_array(SystemUserRole::ROLE_ADMIN, $rolesIds)) {
            $isAdmin = true;
        }

        if (isset($params['id']) && !empty($params['id'])) {
            $model = $customModel->find($params['id']);

            $followUserArray = app(RightService::class)->getCustomViews($userId);
            // 防止越权
            if ($followUserArray != 'all' && !in_array($model->follow_user_id, array_keys($followUserArray))) {
                return $this->apiReturn(static::ERROR, [], '无客户权限');
            }
            $model['mobile'] = app(CustomerService::class)->decrypt($model['mobile_jiami']);
            // if (!$isAdmin && $model['follow_user_id'] != $userId) {
            //     $model['mobile'] = substr($model['mobile'],0 ,3). '****'. substr($model['mobile'],7 ,4);
            // }
            if (empty($model['sex'])) unset($model['sex']);
            if (empty($model['age'])) unset($model['age']);
            if (empty($model['marry'])) unset($model['marry']);
            if (empty($model['work'])) unset($model['work']);
            if (empty($model['live_area'])) unset($model['live_area']);
            if (empty($model['household_area'])) unset($model['household_area']);
            if ($model['income'] == 0) unset($model['income']);
            if ($model['amount'] == 0) unset($model['amount']);
            if (empty($model['follow_status'])) unset($model['follow_status']);
            if (empty($model['star'])) unset($model['star']);
            if (empty($model['city'])) unset($model['city']);
            unset($model['remark']);

            // 客户操作日志大杂烩
            $logmodel = new CustomerLog();
            $logList = $logmodel->getLogListByCustomerId($params['id'], 100);
            foreach ($logList as $key => $log) {
                $log->color = $color[($log->type % 5) + 1];
                $log->type = $log->username;
                $log->day = $log->create_time;
                $logList[$key] = $log;
            }
            $data['fromInfo'] = $customGourpDict[$dictModel::TYPE_SOURCE][$model['source']];
            $data['createTime'] = $model['create_time'];
        } else { 
            $model = new Customer();
        }
        $data['customInfo'] = $model;
        $data['logList'] = $logList;

        return $this->apiReturn(static::OK, $data);
    }


    /**
     * 修改用户信息
     */
    public function edit(Request $request)
    {
        $params = $request->all();

        unset($params['notices']);
        unset($params['update_time']);

        $operUserId = $request->session()->get('user_id');

        $customModel = new Customer();

        DB::beginTransaction();

        if (isset($params['id'])) {
            $customId = $params['id'];
            $service = app(CustomerService::class);
            $customModel = $customModel->find($params['id']);
            if (strpos($params['mobile'], '*') !== false) {
                $params['mobile'] = app(CustomerService::class)->decrypt($params['mobile_jiami']);
            }
            $oldInfo = clone ($customModel);
            if ($params['follow_status'] == 10 && $oldInfo['follow_status'] != $params['follow_status']) {
                return $this->apiReturn(static::ERROR, [], "禁止手动修改已上门状态");
            }
            $params['mobile_jiami'] = app(CustomerService::class)->encrypt($params['mobile']);
            $params['mobile_md5'] = md5($params['mobile']);
            $saveData = $params;
            if ($params['remark']) {
                $saveData['follow_times'] = $oldInfo['follow_times'] + 1;
            }
            unset($saveData['mobile']);
	        $saveData['age'] = intval($saveData['age']);
            $saveData['amount'] = floatval($saveData['amount']);
            $saveData['remark'] = $saveData['remark'] . PHP_EOL .  strval($oldInfo['remark']);
            $saveData['star'] = intval($saveData['star']);
            $flag = $customModel->update($saveData);
            if (!$flag) {
                DB::rollBack();
                return $this->apiReturn(static::ERROR, [], "基本信息保存失败");
            }
            $diff = $service->diff($oldInfo, $customModel);
            if ($diff['star']) {
                $logModel = new CustomerLog();
                $flag = $logModel->saveLog($logModel::TYPE_STAR, $params['id'], $diff['star']['old'], $diff['star']['new'], $operUserId, strval($params['remark']));
                if (!$flag) {
                    DB::rollBack();
                    return $this->apiReturn(static::ERROR, [], "星级变化保存失败");
                }
            }
            if ($diff['follow_status']) {
                $logModel = new CustomerLog();
                $flag = $logModel->saveLog($logModel::TYPE_FOLLOW, $params['id'], $diff['follow_status']['old'], $diff['follow_status']['new'], $operUserId, strval($params['remark']));
                if (!$flag) {
                    DB::rollBack();
                    return $this->apiReturn(static::ERROR, [], "跟进变化保存失败");
                }
                $customModel->follow_time = time();
                $customModel->save();
                if (!$flag) {
                    DB::rollBack();
                    return $this->apiReturn(static::ERROR, [], "跟进时间保存失败");
                }
            } else if (!empty($params['remark'])) {
                $logModel = new CustomerLog();
                $flag = $logModel->saveLog($logModel::TYPE_FOLLOW, $params['id'], intval($params['follow_status']), intval($params['follow_status']), $operUserId, $params['remark']);
                if (!$flag) {
                    DB::rollBack();
                    return $this->apiReturn(static::ERROR, [], "跟进变化保存失败");
                }
                $customModel->follow_time = time();
                $customModel->save();
                if (!$flag) {
                    DB::rollBack();
                    return $this->apiReturn(static::ERROR, [], "跟进时间保存失败");
                }
            }
            unset($diff['star']);
            unset($diff['follow_status']);
            if (count($diff) > 0) {
                $logModel = new CustomerLog();
                $flag = $logModel->saveLog($logModel::TYPE_EDIT, $params['id'], '', '', $operUserId, '');
                $logmodel = new SystemLog();
                $logmodel->saveLog($logmodel::TYPE_EDIT, $params['id'], json_encode($diff), '', $operUserId, strval($params['config']['remark']));
            }
            $customId = $params['id'];
        } else {
            $params['add_user_id'] = $operUserId;
            $params['apply_time'] = time();
            $params['assign_time'] = time();
            // 转介绍
            $params['user_from'] = empty($params['introid']) ? CustomerService::ASSIGN_TYPE_SELF : CustomerService::ASSIGN_TYPE_INTRO;
            $params['follow_user_id'] = $operUserId;
            $params['mobile_md5'] = md5($params['mobile']);

            $customModel = new Customer();
            $has = $customModel->where('mobile_md5', $params['mobile_md5'])->count();
            if ($has > 0) {
                return $this->apiReturn(static::ERROR, [], "手机号已存在");
            } 

            $params['mobile_jiami'] = app(CustomerService::class)->encrypt($params['mobile']);
            if ($params['follow_status']) {
                $params['follow_time'] = time();
            }
            unset($params['mobile']);
            $custom = $customModel->create($params);
            if (!$custom->id) {
                DB::rollBack();
                return $this->apiReturn(static::ERROR, [], "客户录入失败");
            }
            $logModel = new CustomerLog();
            if ($params['introid']) {
                $flag = $logModel->saveLog($logModel::TYPE_INTRO, $custom->id, $params['introid'], '', $operUserId, "转介绍");
                $flag = $logModel->saveLog($logModel::TYPE_INTRO, $params['introid'], $custom->id, '', $operUserId, "转介绍");
            } else {
                $flag = $logModel->saveLog($logModel::TYPE_IN, $custom->id, '', '', $operUserId, "手动录入");
            }
            if (!$flag) {
                DB::rollBack();
                return $this->apiReturn(static::ERROR, [], "客户录入失败.");
            }
            if ($params['star']) {
                $logModel = new CustomerLog();
                $flag = $logModel->saveLog($logModel::TYPE_STAR, $custom->id, 0, $params['star'], $operUserId, strval($params['remark']));
                if (!$flag) {
                    DB::rollBack();
                    return $this->apiReturn(static::ERROR, [], "星级变化保存失败");
                }
            }
            if ($params['follow_status']) {
                $logModel = new CustomerLog();
                $flag = $logModel->saveLog($logModel::TYPE_FOLLOW, $custom->id, 0, $params['follow_status'], $operUserId, strval($params['remark']));
                if (!$flag) {
                    DB::rollBack();
                    return $this->apiReturn(static::ERROR, [], "跟进变化保存失败");
                }
            }
            
            $customId = $custom->id;
        }

        if ($params['remark']) {
            $customerRemarkLogModel = new CustomerRemarkLog();
            $customerRemarkLogModel->saveLog($customId, $params['remark'], $operUserId);
        }

        DB::commit();
        return $this->apiReturn(static::OK, [], "操作成功");
    }


    /**
     * 客户分配
     */
    public function assign(Request $request)
    {
        $params = $request->all();
        $operUserId = $request->session()->get('user_id');

        $followUserIds = $params['followUserId'];
        $customIds = $params['customIds'];

        $customService = app(CustomerService::class);
        $userService = new UserService();
        $index = 0;
        
        foreach ($customIds as $customId) {
            $custom = Customer::find($customId);
            $followUserId = array_shift($followUserIds);
            Log::info("I1697463569 分配的用户:".$customId ." | " . $custom->follow_user_id . " | " .$followUserId . " | " . json_encode($followUserIds));
            if ($custom->follow_user_id == $followUserId) {
                // 把老的放回到队列换一个新的出来
                $oldFollowUserId = $followUserId;
                $followUserId = array_shift($followUserIds);
                array_push($followUserIds, $oldFollowUserId);
                Log::info("I1697463569 重新分配的用户:".$customId ." | " . $custom->follow_user_id . " | " .$followUserId . " | " . json_encode($followUserIds));
            }
            if (empty($followUserId)) {
                continue;
            }
            $assginLeftCount = $userService->getAssginLeftTime($followUserId);
            if ($assginLeftCount <= 0) {
                $userInfo = SystemUser::find($followUserId);
                return $this->apiReturn(static::ERROR, [], "分配失败,".$userInfo->name."可分配的名额到达上限");
            }
            $index++;
            $flag = $customService->assign($customId, $followUserId, $operUserId);
            if (!$flag) {
                return $this->apiReturn(static::ERROR, [], "部分客户分配失败,请刷新重试");
            }
            array_push($followUserIds, $followUserId);
        }
        return $this->apiReturn(static::OK);
    }

    /**
     * 客户认领
     */
    public function get(Request $request)
    {
        $params = $request->all();
        $operUserId = $request->session()->get('user_id');
        $customId = $params['id'];

        $customService = app(CustomerService::class);
        $userService = app(UserService::class);

        $configModel = new CustomerRuleConfig();
        $rule = $configModel->find(9);
        $status = $rule->status;
        if ($status == 1) {
            $config = json_decode($rule->config, true);
            $times = $config['times'];
            $stime = date('Y-m-d')." " . $times[0];
            $etime = date('Y-m-d')." " . $times[1];
            $ntime = date('Y-m-d H:i:s');
            if ($ntime < $stime || $ntime > $etime) {
                return $this->apiReturn(static::ERROR, [], "认领时间为 : ".$times[0] . " - " . $times[1]);
            }
        }

        $leftCount = $userService->getGetLeftTime($operUserId);
        if ($leftCount <= 0) {
            return $this->apiReturn(static::ERROR, [], "客户认领失败,超过今日认领上限");
        }
        if (!$customService->canAssign($customId)) {
            $model = new Customer();
            $custom = $model->find($customId);
            return $this->apiReturn(static::ERROR, [], $custom['name']."(".$customId.")正在跟进中，暂时无法分配");
        }

        $flag = $customService->get($customId, $operUserId, $operUserId);
        if (!$flag) {
            return $this->apiReturn(static::ERROR, [], "客户认领失败,请刷新重试");
        }

        return $this->apiReturn(static::OK);
    }


    /**
     * 分配记录LIST
     */
    public function assignlist(Request $request)
    {

        $params = $request->all();
        $userModel = new SystemUser();
        $logModel = new CustomerLog();

        $followUserList = $userModel->getAllUserWithDel();
        $followUserArray = ($followUserList)->mapWithKeys(function ($item) {
            return [$item['id'] => $item['name']];
        })->toArray();

        $list = $logModel->getLogListByCustomerIdAndType($params['customId'], $logModel::TYPE_ASSIGN, $params);
        foreach ($list as $key => $item) {
            $item['old_user'] = $followUserArray[$item['before']];
            $item['new_user'] = $followUserArray[$item['after']];
            $item['oper_user'] = $followUserArray[$item['user_id']];
            $item['type'] = CustomerService::ASSIGN_TYPE_MAPPING[$item['remark']];
            $list[$key] = $item;
        }
        $data['list'] = $list;
        $data['total'] = $logModel->getCountByCustomerIdAndType($params['customId'], $logModel::TYPE_ASSIGN);
        return $this->apiReturn(static::OK, $data);
    }



    /**
     * 跟进记录LIST
     */
    public function followlist(Request $request)
    {

        $params = $request->all();
        $dict = new SystemDict();
        $userModel = new SystemUser();
        $logModel = new CustomerLog();

        $followUserList = $userModel->getAllUserWithDel();
        $followUserArray = collect($followUserList)->mapWithKeys(function ($item) {
            return [$item['id'] => $item['name']];
        })->toArray();

        $customGourpDict = $dict->getListByGroup($dict::GROUP_CUSTOM); // 获取城市信息配置

        $list = $logModel->getLogListByCustomerIdAndType($params['customId'], $logModel::TYPE_FOLLOW, $params);
        foreach ($list as $key => $item) {
            $item['old_status'] = $customGourpDict[$dict::TYPE_FOLLOW][$item['before']];
            $item['new_status'] = $customGourpDict[$dict::TYPE_FOLLOW][$item['after']];
            $item['oper_user'] = $followUserArray[$item['user_id']];
            $list[$key] = $item;
        }
        $data['list'] = $list;
        $data['total'] = $logModel->getCountByCustomerIdAndType($params['customId'], $logModel::TYPE_FOLLOW);
        return $this->apiReturn(static::OK, $data);
    }



    /**
     * 星级变化记录
     */
    public function starlist(Request $request)
    {

        $params = $request->all();
        $dict = new SystemDict();
        $userModel = new SystemUser();
        $logModel = new CustomerLog();

        $followUserList = $userModel->getAllUserWithDel();
        $followUserArray = collect($followUserList)->mapWithKeys(function ($item) {
            return [$item['id'] => $item['name']];
        })->toArray();
        $customGourpDict = $dict->getListByGroup($dict::GROUP_CUSTOM); // 获取城市信息配置

        $list = $logModel->getLogListByCustomerIdAndType($params['customId'], $logModel::TYPE_STAR, $params);
        foreach ($list as $key => $item) {
            $item['old_value'] = $customGourpDict[$dict::TYPE_STAR][$item['before']];
            $item['new_value'] = $customGourpDict[$dict::TYPE_STAR][$item['after']];
            $item['oper_user'] = $followUserArray[$item['user_id']];
            $list[$key] = $item;
        }
        $data['list'] = $list;
        $data['total'] = $logModel->getCountByCustomerIdAndType($params['customId'], $logModel::TYPE_STAR);
        return $this->apiReturn(static::OK, $data);
    }


    /**
     * 通话记录
     */
    public function calllist(Request $request)
    {

        $params = $request->all();
        $logModel = new CustomerCallLog();

        $userModel = new SystemUser();
        $followUserList = $userModel->getAllUserWithDel();
        $followUserArray = collect($followUserList)->mapWithKeys(function ($item) {
            return [$item['id'] => $item['name']];
        })->toArray();

        $list = $logModel->getLogListByCustomerId($params['customId'], $params);
        foreach ($list as $key => $item) {
            $item['status_name'] = $item['status'] == 1 ? '已接通' :'未接通';
            $item['total_duration'] = $item['status'] == 1 ? $item['total_duration'] : '-';
            $item['file'] = "/api/custom/viewrecord?id=" . $item['id'];
            $item['oper_user'] = $followUserArray[$item['user_id']];
            $list[$key] = $item;
        }
        $data['list'] = $list;
        $data['total'] = $logModel->getCountByCustomerId($params['customId']);
        return $this->apiReturn(static::OK, $data);
    }




    /**
     * 锁定
     */
    public function lock(Request $request) {
        $userId = $request->session()->get('user_id');
        $params = $request->all();
        if ($params['lock'] == 1) {
            $configModel = new CustomerRuleConfig();
            $rule = $configModel->find(11);
            $status = $rule->status;
            if ($status == 1) {
                $config = json_decode($rule->config, true);
                $num = $config['num'];
                $cnt = Customer::where('follow_user_id', $userId)->where('lock', 1)->count();
                if ($cnt > $num) {
                    return $this->apiReturn(static::ERROR, [], "不能锁定超过".$num."个客户");
                }
            }
        }
        DB::beginTransaction();
        $model = Customer::find($params['id']);
        $model->lock = $params['lock'];
        $model->save();
        $logmodel = new CustomerLog();
        $logmodel->saveLog($model->lock == 1 ? $logmodel::TYPE_LOCK: $logmodel::TYPE_UNLOCK, $params['id'], '', '', $userId, '');
        DB::commit();
        return $this->apiReturn(static::OK);
    }

    /**
     * 移入公海 
     */
    public function giveup(Request $request) {
        $userId = $request->session()->get('user_id');
        $params = $request->all();
        $custom = Customer::find($params['id']);
        $custom->last_follow_user_id = $custom->follow_user_id;
        $custom->save();
        $customService = app(CustomerService::class);
        $customService->giveup($params['id'], $userId, '移入公海');
        return $this->apiReturn(static::OK);
    }


    /**
     * 批量移入公海 
     */
    public function batchgiveup(Request $request) {
        $userId = $request->session()->get('user_id');
        $params = $request->all();
        $customIds = $params['customIds'];
        DB::beginTransaction();
        foreach ($customIds as $customId) {
            $custom = Customer::find($customId);
            $custom->last_follow_user_id = $custom->follow_user_id;
            $custom->save();
            $customService = app(CustomerService::class);
            $customService->giveup($customId, $userId, '移入公海');
        }
        DB::commit();
        return $this->apiReturn(static::OK);
    }


    /**
     * 批量领取
     */
    public function batchget(Request $request) {
        
        $configModel = new CustomerRuleConfig();
        $rule = $configModel->find(9);
        $status = $rule->status;
        if ($status == 1) {
            $config = json_decode($rule->config, true);
            $times = $config['times'];
            $stime = date('Y-m-d')." " . $times[0];
            $etime = date('Y-m-d')." " . $times[1];
            $ntime = date('Y-m-d H:i:s');
            if ($ntime < $stime || $ntime > $etime) {
                return $this->apiReturn(static::ERROR, [], "认领时间为 : ".$times[0] . " - " . $times[1]);
            }
        }
        $userId = $request->session()->get('user_id');
        $params = $request->all();
        $customIds = $params['customIds'];
        $customService = app(CustomerService::class);
        $userService = app(UserService::class);
        $leftCount = $userService->getGetLeftTime($userId);
        if ($leftCount < count($customIds)) {
            return $this->apiReturn(static::ERROR, [], "认领失败,超过今日认领上限,今日剩余".$leftCount."认领名额");
        }
        DB::beginTransaction();
        foreach ($customIds as $customId) {
            if (!$customService->canAssign($customId)) {
                $model = new Customer();
                $custom = $model->find($customId);
                return $this->apiReturn(static::ERROR, [], $custom['name']."(".$customId.")正在跟进中，暂时无法分配");
            }
            $customService->get($customId, $userId, $userId);
        }
        DB::commit();
        return $this->apiReturn(static::OK);
    }

    /**
     * 设为重要
     */
    public function important(Request $request) {
        $userId = $request->session()->get('user_id');
        $params = $request->all();
        DB::beginTransaction();
        $model = Customer::find($params['id']);
        $model->important = $params['important'];
        $model->save();
        $logmodel = new CustomerLog();
        $logmodel->saveLog($model->important == 1 ? $logmodel::TYPE_IMPORTANT : $logmodel::TYPE_NOT_IMPORTANT, $params['id'], '', '', $userId, '');
        DB::commit();
        return $this->apiReturn(static::OK);
    }

    /**
     * 设为无效
     */
    public function lahei(Request $request) {
        $userId = $request->session()->get('user_id');
        $params = $request->all();
        $model = Customer::find($params['id']);
        $model->status = $params['status'];
        $model->follow_user_id = 0;
        $model->save();
        $logmodel = new CustomerLog();
        $logmodel->saveLog($model->status == 1 ? $logmodel::TYPE_NOT_UNVALID : $logmodel::TYPE_UNVALID, $params['id'], '', '', $userId, '');
        return $this->apiReturn(static::OK);
    }

    /**
     * 批量导入用户
     */
    public function upload(Request $request) {
        $files = ($request->file());
        $fileName = array_keys($files)[0];
        $params = $request->all();
        $source = intval($params['source']);
        if (!in_array($fileName, ['CustomerPool', 'CustomerNewpool'])) {
            return $this->apiReturn(static::ERROR, [], "入口异常,请刷新重试");
        }
        try {
            $data = Excel::toArray(new CustomerModel, $request->file($fileName))[0];
        } catch (\Exception $e) {
            return $this->apiReturn(static::ERROR, [], "文件格式异常请重试");
        }
        array_shift($data);
        if (empty($data)) {
            return $this->apiReturn(static::ERROR, [], "客户信息为空");
        }
        $service = app(\App\Services\CustomerService::class);
        $error = [];
        $customs = [];
        $dict = new SystemDict();
        $customGourpDict = $dict->getListByGroup($dict::GROUP_CUSTOM); // 获取城市信息配置
        $index = 1;
        $mobiles = [];
        $times = 0;
        $success = 0;
        foreach ($data as $row) {
            $customCheck = $service->genCustomByExcelRow($row, $customGourpDict);
            if ($customCheck['status'] == 1) {
                if ($fileName == 'CustomerNewpool') {
                    $customCheck['custom']['user_from'] = 1;
                } else if ($fileName == 'CustomerPool') {
                    $customCheck['custom']['user_from'] = 2;
                }
                $customCheck['custom']['mobile_md5'] = md5($customCheck['custom']['mobile']);
                $customCheck['custom']['mobile_jiami'] = app(CustomerService::class)->encrypt($customCheck['custom']['mobile']);
                $customCheck['custom']['source'] = intval($source) ? intval($source) : intval(Constants::EXPORT_CHANNEL);
                $customCheck['custom']['star'] = $customCheck['custom']['star'] ? $customCheck['custom']['star'] : -1;
                if (in_array($customCheck['custom']['mobile'], $mobiles) || count(Customer::where('mobile_jiami', $customCheck['custom']['mobile_jiami'])->get()) > 0) {
                    $times++;
                } else {
                    $success++;
                    unset($customCheck['custom']['mobile']);
                    $customs[] = $customCheck['custom'];
                }
            } else {
                $error[] = "第".$index."行:".implode(PHP_EOL, $customCheck['error']);
            }
            $index++;
        }
        if (empty($error)) {
            foreach ($customs as $custom) {
                $id = (new Customer())->insertGetId($custom);
                if ($custom['remark']) {
                    $logModel = new CustomerLog();
                    $customerRemarkLogModel = new CustomerRemarkLog();
                    $customerRemarkLogModel->saveLog($id, $custom['remark'], 0);
                    $logModel->saveLog($logModel::TYPE_FOLLOW, $id, 0, 0, 0, $custom['remark']);
                }
            }
            return $this->apiReturn(static::OK, [], '导入成功'.$success.'个客户, 重复'.$times.'个');
        } else {
            return $this->apiReturn(static::ERROR, [], implode(PHP_EOL, $error));
        }
        
    }

    /**
     * 待办事项列表
     */
    public function getNoticeList (Request $request) {
        $params = $request->all();
        $userId = $request->session()->get('user_id');
        $customId = $params['id'];
        $notice = new Notice();
        $list = $notice->getListByUserIdAndCustomer($customId, $userId, 3);
        $color = ['', 'green', 'blue', 'purple', 'pinkpurple', 'red'];
        $dict = new SystemDict();
        $customGourpDict = $dict->getListByGroup($dict::GROUP_CUSTOM); // 获取城市信息配置
        foreach ($list as $key => $item) {
            $item['color'] = $color[$item['type']];
            $item['type'] = $customGourpDict[$dict::TYPE_NOTICE][$item['type']];
            $item['day'] = date('Y-m-d H:i:s', $item['date']);
            $list[$key] = $item;
        }
        return $this->apiReturn(static::OK, ['list'=>$list]);
    }

    /**
     * 增加代办事项
     */
    public function addNotices(Request $request) {
        $userId = $request->session()->get('user_id');
        $params = $request->all();
        $customId = $params['id'];
        $notices = $params['notices'];
        DB::beginTransaction();
        foreach ($notices as $notice) {
            if (empty($notice['date'])) {
                DB::rollBack();
                return $this->apiReturn(static::ERROR, [], "请选择日期");
            }
            if (empty($notice['remark'])) {
                DB::rollBack();
                return $this->apiReturn(static::ERROR, [], "请输入备注");
            }
            $noticeModel = new Notice();
            $noticeModel->date = strtotime($notice['date']);
            $noticeModel->custom_id = $customId;
            $noticeModel->follow_user_id = $userId;
            $noticeModel->type = $notice['type'];
            $noticeModel->remark = $notice['remark'];
            $flag = $noticeModel->save();
            if (!$flag) {
                DB::rollBack();
                return $this->apiReturn(static::ERROR, [], "日程写入失败");
            }
        }
        DB::commit();
        return $this->apiReturn(static::OK);

    }
    
    /**
     * 添加点评
     */
    public function addDianping(Request $request) {
        $params = $request->all();
        $customId = $params['id'];
        $userId = $request->session()->get('user_id');
        $logmodel = new CustomerLog();
        $logmodel->saveLog($logmodel::TYPE_DIANPING, $params['id'], '', '', $userId, $params['remark']);
        $custom = Customer::find($customId);
        $noticeModel = new Notice();
        $noticeModel->date = time();
        $noticeModel->custom_id = $customId;
        $noticeModel->follow_user_id = $custom['follow_user_id'];
        $noticeModel->type = 5;
        $noticeModel->remark = $params['remark'];
        $noticeModel->save();
        DB::commit();
        return $this->apiReturn(static::OK);
    }

    /**
     * 添加回款
     */
    public function addBack(Request $request) {
        $params = $request->all();
        $userId = $request->session()->get('user_id');
        $customId = $params['id'];
        $model = Customer::find($params['id']);
        $backmodel = new CustomerBack();
        if (empty($params['date'])) {
            return $this->apiReturn(static::ERROR, [], "请输入放款时间");
        }
        if (empty($params['amount'])) {
            return $this->apiReturn(static::ERROR, [], "请输入放款金额");
        }
        if (empty($params['agency_fee'])) {
            return $this->apiReturn(static::ERROR, [], "请输入点位");
        }
        if (empty($params['realmoney'])) {
            return $this->apiReturn(static::ERROR, [], "请输入实际创收");
        }
        if (empty($params['quanzheng'])) {
            return $this->apiReturn(static::ERROR, [], "请输入权证");
        }
        $backmodel->custom_id = $params['id'];
        $backmodel->date = strtotime($params['date']);
        $backmodel->amount = $params['amount'];
        $backmodel->fee = $params['agency_fee'];
        $backmodel->real_amount = $params['realmoney'];
        $backmodel->remark = $params['remark'];
        $backmodel->cost = $params['cost'];
        $backmodel->quanzheng = $params['quanzheng'];
        $backmodel->apply_date = intval($model->apply_time);
        $backmodel->apply_amount = floatval($model->amount);
        $backmodel->follow_user_id = intval($model->follow_user_id);
        $backmodel->oper_user_id = $userId;
        $backmodel->hetong = $params['hetong'];
        $backmodel->product_id= $params['product_id'];
        $backmodel->status = 0;
        $backmodel->save();
        $logModel = new CustomerLog();
        // 发起审批
        $custom = Customer::find($customId);
        $user = SystemUser::find(intval($userId));
        $team = SystemTeam::find(intval($user['team_id']));
        $quanzheng = SystemUser::find(intval($params['quanzheng']));
        $parent = SystemUser::find(intval($user['parent']));
        $product = Product::find(intval($params['product_id']));
        $form = [
            '客户姓名' => $custom['name'],
            '放款金额' => $params['amount'] . '元',
            '点位金额' => $params['agency_fee'] . '%',
            '实际创收' => $params['realmoney'] . '元',
            '成本费用' => $params['cost'] . '元',
            '跟进顾问' => $user['name'],
            '部门' => $team['name'],
            '权证经理' => $quanzheng['name'],
            '合同编号' => $params['hetong'],
            '产品' =>    $product['name'],
        ];
        $approveUserId = [$user['parent_id'], $parent['parent_id'], 1];
        $approveService = new ApproveService();
        $approveService->createApprove($backmodel->id, $approveService::TYPE_BACK, $form, $userId, $approveUserId);
        $flag = $logModel->saveLog($logModel::TYPE_BACK, $params['id'], '', '', $userId, "金额:".$params['amount']);
        return $this->apiReturn(static::OK);

    }


    /**
     * 编辑回款
     */
    public function editBack(Request $request) {
        $params = $request->all();
        $userId = $request->session()->get('user_id');
        $backmodel = new CustomerBack();
        $backmodel = $backmodel->find($params['id']);
        if (empty($params['id']) || empty($backmodel)) {
            return $this->apiReturn(static::ERROR, [], "参数错误,请刷新重试");
        }
        if (empty($params['date'])) {
            return $this->apiReturn(static::ERROR, [], "请输入放款时间");
        }
        if (empty($params['amount'])) {
            return $this->apiReturn(static::ERROR, [], "请输入放款金额");
        }
        if (empty($params['agency_fee'])) {
            return $this->apiReturn(static::ERROR, [], "请输入点位");
        }
        if (empty($params['realmoney'])) {
            return $this->apiReturn(static::ERROR, [], "请输入实际创收");
        }
        $backmodel->date = strtotime($params['date']);
        $backmodel->amount = $params['amount'];
        $backmodel->fee = $params['agency_fee'];
        $backmodel->real_amount = $params['realmoney'];
        $backmodel->remark = $params['remark'];
        $backmodel->cost = $params['cost'];
        $backmodel->hetong = $params['hetong'];
        $backmodel->product_id= $params['product_id'];
        $backmodel->save();
        return $this->apiReturn(static::OK);

    }

    /**
     * 编辑回款
     */
    public function delBack(Request $request) {
        $params = $request->all();
        $backmodel = new CustomerBack();
        $backmodel = $backmodel->find(intval($params['id']));
        $backmodel->delete();
        return $this->apiReturn(static::OK);

    }
    /**
     * 回款列表
     */
    public function backlist(Request $request)
    {

        $params = $request->all();
        $dict = new SystemDict();
        $userModel = new SystemUser();
        $model = new CustomerBack();

        $followUserList = $userModel->getAllUserWithDel();
        $followUserArray = collect($followUserList)->mapWithKeys(function ($item) {
            return [$item['id'] => $item['name']];
        })->toArray();
        $customGourpDict = $dict->getListByGroup($dict::GROUP_CUSTOM); // 获取城市信息配置

        $list = $model->getLists($params);
        foreach ($list as $key => $item) {
            $item['apply_amount'] = $item['apply_amount'] ? $item['apply_amount'] : '';
            $item['fee'] = $item['fee'] . '%';
            $item['apply_date'] = $item['apply_date'] ? date('Y-m-d H:i:s', $item['apply_date']) : '';
            $item['date'] = $item['date'] ? date('Y-m-d H:i:s', $item['date']) : '';
            $item['new_value'] = $customGourpDict[$dict::TYPE_STAR][$item['after']];
            $item['oper_user'] = $followUserArray[$item['oper_user_id']];
            $item['status'] = ApproveService::STATUS_MAPPING[$item['status']];
            $list[$key] = $item;
        }
        $data['list'] = $list;
        $data['total'] = $model->getCount($params);
        return $this->apiReturn(static::OK, $data);
    }
    

    /**
     * 认领后未跟进
     */
    public function follownum(Request $request)
    {
        $userId = $request->session()->get('user_id');
        $data = ['custom_num'=>0, 'approve_num'=>0];
        if ($userId > 0) {
            $model = new Customer();
            $data['custom_num'] =  $model->getWaitForFollow($userId);
            $service = new ApproveService();
            $data['approve_num'] = $service->noticeNum($userId);
        }
        return $this->apiReturn(static::OK, $data);
    }

    /**
     * 打电话
     */
    public function call(Request $request)
    {
        $params = $request->all();
        $userId = $request->session()->get('user_id');
        $customId = $params['id'];
        $user = SystemUser::find($userId);
        $custom =  Customer::find($customId);
        if (empty($user['token'])) {
            return $this->apiReturn(static::ERROR, [], '请登录客户端');
        }
        $logModel = new CustomerLog();
        $callLogMdoel = new CustomerCallLog();
        $logid = $logModel->saveLog(CustomerLog::TYPE_CALL, $customId, 0, 0, $userId);
        $callId = $callLogMdoel->saveLog($logid, $customId, $userId);
        $client = new Client();
        $custom['mobile'] = app(CustomerService::class)->decrypt($custom['mobile_jiami']);
        $client->post("http://127.0.0.1:8990", [
            'form_params'=> [
                'data' => json_encode(
                    [
                        'action'=>'notify',
                        'admin_token' => '61282c1c75e39cad150f7e704f278ade',
                        'user_token' => $user['token'],
                        'message' => json_encode([
                            'action' => 'make_call',
                            'mobile' => $custom['mobile'],
                            'name' => $custom['name'],
                            'id'     => $callId
                        ])
                    ]
                )
            ]
        ]);
        return $this->apiReturn(static::OK);
    }

    /**
     * 查看录音
     */
    public function viewrecord(Request $request) {
        $params = $request->all();
        $userId = $request->session()->get('user_id');
        $id = $params['id'];
        $file_size = 0;
        $record = CustomerCallLog::find($id);

        $followUserArray = app(RightService::class)->getCustomViews($userId);
        // 判断权限
        if ($followUserArray != 'all' && !array_key_exists($record['user_id'], $followUserArray) ) {
            return;
        }

        header("Content-type:audio/mp3");//文件扩展名，其他扩展参数参考下图
        header("Accept-Ranges:bytes");
        if ($record['file']) {
            $n = "/data/www/crmcallfile/record/".$record['file'];
            header("Accept-Length:".$file_size);
            $file_size = filesize($n);//获取原文件大小
            readfile($n);
        }
    }

    
    /**
     * 统计数据 
     */
    public function data(Request $request)
    {
        $userId = $request->session()->get('user_id');
        $userModel = new SystemUser();
        $customModel = new Customer();
        $data = [];
        
        // 筛选项
        $params = $request->all();
        $data['master'] = 0;
        $followUserArray = app(RightService::class)->getCustomViews($userId);
        if ($followUserArray == 'all') {
            $followUserList = $userModel->getAllUser();
            $followUserArray = collect($followUserList)->mapWithKeys(function ($item) {
                return [$item['id'] => $item['name']];
            })->toArray();
            $data['master'] = 1;
        } else {
            $params['users'] = array_keys($followUserArray);
        }
        $data['followUserList'] = app(SelectService::class)->genSelectByKV($followUserArray); // 转成前端的select

        $data['num1'] = $customModel->getCount(array_merge($params, ['notFollow'=>1, 'star'=>-1]));
        $data['num2'] = $customModel->getCount(array_merge($params, ['notFollow'=>6, 'star'=>9]));
        $data['num3'] = $customModel->getCount(array_merge($params, ['notFollow'=>3, 'star'=>10]));
        return $this->apiReturn(static::OK, $data);
    }

    // 上门登记
    public function dengji(Request $request) {
        $params = $request->all();
        $mobile = trim($params['mobile']);
        $guwen = trim($params['guwen']);
        $name = trim($params['name']);
        if (empty($name)) {
            return $this->apiReturn(static::ERROR, [], "来访客户不能为空");
        }
        if (empty($mobile)) {
            return $this->apiReturn(static::ERROR, [], "来访客户手机号不能为空");
        }
        if (!is_numeric($mobile) || strlen($mobile) != 11) {
            return $this->apiReturn(static::ERROR, [], "来访客户手机号必须是11位数字");
        }
        if (empty($guwen)) {
            return $this->apiReturn(static::ERROR, [], "预约顾问不能为空");
        }
        $mobile_jiami = app(CustomerService::class)->encrypt($mobile);
        $customers = Customer::where('mobile_jiami', $mobile_jiami)->get()->toArray();
        if (empty($customers)) {
            return $this->apiReturn(static::ERROR, [], "信息错误，当前信息系统不存在");
        }
        $customerR = [];
        $user = SystemUser::where('name', $guwen)->get()->toArray();
        if (empty($user)) {
            return $this->apiReturn(static::ERROR, [], "信息错误，顾问在系统不存在");
        } else {
            $userId = $user[0]['id'];
            foreach ($customers as $customer) {
                if ($customer['follow_user_id'] == $userId) {
                    $customerR = $customer;
                    break;
                }
            }
        }
        if (empty($customerR)) {
            $customerR = $customers[0];
        }
        $customer = Customer::find($customerR['id']);
        $logModel = new CustomerLog();
        $followStatus = 10;
        $logModel->saveLog($logModel::TYPE_FOLLOW, $customer['id'], $customer['follow_status'], $followStatus, $userId);
        $customer->follow_time = time();
        $customer->follow_status = $followStatus;
        $customer->save();
        $dengjiLogModel = new CustomerDengjiLog();
        $dengjiLogModel->insert(
            [
                'mobile' => $mobile_jiami,
                'guwen' => $guwen,
                'name' => $name,
                'customer_id' => $customer['id'],
                'user_id' => $userId
            ]
        );
        return $this->apiReturn(static::OK);
    }
}
