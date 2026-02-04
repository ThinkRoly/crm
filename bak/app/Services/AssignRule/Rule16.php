<?php

namespace App\Services\AssignRule;
use App\Services\CustomerService;


/**
 * 客户分配相关的service
 */
class Rule16
{
    public function handle($config) {
        echo "RULE16: 配置" . json_encode($config).PHP_EOL;
        if (date('H') != 4) {
            echo "RULE16: 不是凌晨4点".PHP_EOL;
            return ['status'=>1];
        }
        $customService = app(CustomerService::class);
        $time = time() - 24 * 3600 * 7;
        $sql = "select * from customer where follow_user_id > 0 and follow_status in (10) and assign_time < ? and follow_time < ? and user_from != 1  and follow_time < assign_time  and `lock` = 0";
        $data = app('db')->select($sql, [$time, $time]);
        foreach ($data as $item) {
            echo "RULE16: 超过已上门7天没有跟进，流入公共池, id: ".$item->id  . "|" . $item->follow_user_id .PHP_EOL;
            $customService->giveup($item->id, 0, '已上门客户超过7天没有跟进，流入公共池');
        }

        $time = time() - 24 * 3600 * 15;
        $sql = "select * from customer where follow_user_id > 0 and follow_status in (4) and assign_time < ? and follow_time < ? and user_from != 1  and follow_time < assign_time  and `lock` = 0";
        $data = app('db')->select($sql, [$time, $time]);
        foreach ($data as $item) {
            echo "RULE16: 超过已签约15天没有跟进，流入公共池, id: ".$item->id  . "|" . $item->follow_user_id .PHP_EOL;
            $customService->giveup($item->id, 0, '已签约客户超过15天没有跟进，流入公共池');
        }

        $time = time() - 24 * 3600 * 100;
        $sql = "select * from customer where follow_user_id > 0 and follow_status in (5) and assign_time < ? and follow_time < ? and user_from != 1  and follow_time < assign_time  and `lock` = 0";
        $data = app('db')->select($sql, [$time, $time]);
        foreach ($data as $item) {
            echo "RULE16: 超过银行已放款100天没有跟进，流入公共池, id: ".$item->id  . "|" . $item->follow_user_id .PHP_EOL;
            $customService->giveup($item->id, 0, '银行已放款客户超过100天没有跟进，流入公共池');
        }
        return ['status'=>1];
    }
}
