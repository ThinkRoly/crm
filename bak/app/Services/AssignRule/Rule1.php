<?php

namespace App\Services\AssignRule;

use App\Models\Customer;
use App\Models\CustomerLog;
use App\Services\CustomerService;
use App\Services\UserService;

/**
 * 客户分配相关的service
 */
class Rule1
{
    public function handle($config) {
        echo PHP_EOL;
        // 获取今天还有分配名额的用户
        $customService = app(CustomerService::class);
        $userService = app(UserService::class);
        // 获取所有新数据公共池用户
        $model = new Customer();
        $customers = $model->where('user_from', 1)->where('status', 1)->where('follow_user_id', 0)->where('source', '<>', 6)->get();
        echo "RULE1: 一共" . count($customers) . "待分配用户" .PHP_EOL;
        foreach ($customers as $customer) {
            $data = Customer::select('star')->where('id', '<', $customer->id)->where('mobile_jiami', $customer->mobile_jiami)->get()->toArray();
            if ($data) {
                // 设置无效
                $model = Customer::find($customer->id);
                $model->status = 0;
                //$model->save();
                $logmodel = new CustomerLog();
                //$logmodel->saveLog($logmodel::TYPE_UNVALID, $customer->id, '', '', 0, '重复手机号');
                //continue;
            } 
            
            $users = $userService->getAllAssignUser(true);
            echo "RULE1: 新数据分配的所有用户" . json_encode($users).PHP_EOL;
            if (empty($users)) {
                continue;
            }
            foreach ($users as $userId => $leftCount) {
                $userIdStr = $userId;
                $userId = intval($userId);
                if ($leftCount <= 0) {
                    //continue;
                }
                echo "RULE1: 分配" . $customer->id . ' --> ' . $userId .PHP_EOL;
                $flag = $customService->assign($customer->id, $userId, 0, CustomerService::ASSIGN_TYPE_NEW);
                $users[$userIdStr] = $leftCount + 1;
                break;
            }
            // 把第一位的用户排到最后一位用作轮训
            $key = array_keys($users);
            $users[$key[0]] = array_shift($users);
        }

        $jh_customers = $model->where('user_from', 1)->where('status', 1)->where('follow_user_id', 0)->where('source', 6)->get();
        echo "RULE1: 一共(激活)" . count($jh_customers) . "待分配用户" .PHP_EOL;
        foreach ($jh_customers as $jh_customer) {
            $jh_users = $userService->getAllAssignUser(false);
            echo "RULE1: 新(激活)数据分配的所有用户" . json_encode($jh_users).PHP_EOL;
            if (empty($jh_users)) {
                continue;
            }
            foreach ($jh_users as $jh_userId => $leftCount) {
                $jh_userIdStr = $jh_userId;
                $jh_userId = intval($jh_userId);
                if ($leftCount <= 0) {
                    //continue;
                }
                echo "RULE1: 分配(激活)" . $jh_customer->id . ' --> ' . $jh_userId .PHP_EOL;
                $flag = $customService->assign($jh_customer->id, $jh_userId, 0, CustomerService::ASSIGN_TYPE_NEW);
                $jh_users[$jh_userIdStr] = $leftCount + 1;
                break;
            }
            // 把第一位的用户排到最后一位用作轮训
            $key = array_keys($jh_users);
            $jh_users[$key[0]] = array_shift($jh_users);
        }

        return ['status'=>1];
    }
}
