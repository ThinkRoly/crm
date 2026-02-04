<?php

namespace App\Services\Channel;

use App\Models\Channel;
use App\Models\SystemDict;
use JsonException;
use App\Services\CustomerService;
use App\Services\UserService;
use Illuminate\Support\Facades\Log;
use App\Models\Customer;

/**
 * 普咚花
 */
class PdhChannel
{
    public function getData($channel, $data) {
        $data = $data['data'];
        $key = $channel['config'];
        $iv = $channel['token'];
        $dict = new SystemDict();
        $citys = array_flip($dict->getListByType($dict::TYPE_CITY)); // 获取城市信息配置
        $retData = [];
        if (empty($data)) {
            echo json_encode(['code' => '999999', 'msg' => 'data为空']);
            exit;
        }
        $customer = openssl_decrypt(base64_decode($data), "AES-256-CBC", $key, true, $iv);
        if (empty($customer)) {
            echo json_encode(['code' => '999999', 'msg' => '数据解密失败']);
            exit;
        }
        $customer = json_decode($customer, true);
        if (empty($customer)) {
            echo json_encode(['code' => '999999', 'msg' => '数据解密失败']);
            exit;
        }
        $channel = Channel::find(3);
        $custom = [
            'source' => 3,
            'name' => $customer['name'],
            'city' => intval($citys[str_replace("市", "", $customer['workCity'])]),
            'mobile' => $customer['mobile'],
            'age' => intval($customer['age']),
            'amount' => intval($customer['amount']),
            'apply_time' => time(),
            'qualification' => '',
            'house' => 0,
            'car' => 0,
            'policy' => 0,
            'wage' => 0,
            'funds' => 1,
            'insurance' => 0,
            'credit' => 0,
            'cost' => $channel->cost,
        ];
        if ($customer['house']) {
            if ($customer['house'] == 'NO_HOUSE') {
                $custom['house'] = 1;
            } else {
                $custom['house'] = 2;
            }
        }
        if ($customer['car']) {
            if ($customer['car'] == 'NO_CAR') {
                $custom['car'] = 1;
            } else {
                $custom['car'] = 2;
            }
        }
        if ($customer['insurance']) {
            if ($customer['insurance'] == 'NO_INSURANCE') {
                $custom['policy'] = 1;
            } else {
                $custom['policy'] = 2;
            }
        }
        if ($customer['sex']) {
            if ($customer['sex'] == 'MALE') {
                $custom['sex'] = 1;
            } else {
                $custom['sex'] = 2;
            }
        }
        if ($customer['accumulationFund']) {
            if ($customer['accumulationFund'] == 'NO') {
                $custom['funds'] = 1;
            } else {
                $custom['funds'] = 2;
            }
        }
        if ($customer['social']) {
            if ($customer['social'] == 'NO') {
                $custom['insurance'] = 1;
            } else {
                $custom['insurance'] = 2;
            }
        }
        $custom['mobile_md5'] = md5($custom['mobile']);
        $custom['mobile_jiami'] = app(CustomerService::class)->encrypt($custom['mobile']);
        unset($custom['mobile']);
        $id = (new Customer())->insertGetId($custom);
        Log::info("IN1686510036 新用户: " . $channel['name'] . ":". $id);
        echo json_encode(['code' => '000000', 'msg' => '消耗成功']);
        exit;
    }
}

