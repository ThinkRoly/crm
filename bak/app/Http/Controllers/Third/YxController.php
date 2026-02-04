<?php

namespace App\Http\Controllers\Third;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Customer;
use App\Models\SystemDict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\CustomerService;
use App\Services\UserService;

class YxController extends Controller
{
    private $sourceId = 3;

    private function getRealData($request) {
        $data = $request->json()->all();
        Log::info("IN1663039375 push参数: " . json_encode($request->json()->all()));
        $channel = Channel::find($this->sourceId);
        if (empty($data)) {
            return [];
        } else {
            $data['name'] = $this->decode($data['name'], $channel['token']);
            return $data;
        }
    }

    public function checkUser(Request $request) {
        $params = $request->json()->all();
        Log::info("IN1663039375 push参数: " . json_encode($request->json()->all()));
        if (empty($params)) {
            return json_encode(['code'=>0, 'msg'=>'校验通过']);
        }

        $customModel = new Customer();
        $has = $customModel->where('mobile_md5', $params['phone_md5'])->count();
        if ($has > 0) {
            return json_encode(['code'=>10001, 'msg'=>'校验不通过']);
        } else {
            return json_encode(['code'=>0, 'msg'=>'校验通过']);
        }
    }

    public function order(Request $request) {
        $channel = Channel::find($this->sourceId);
        $params = $this->getRealData($request);
        if (empty($params)) {
            return json_encode(['code'=>10001, 'msg'=>'订单数据异常']);
        }
        if (empty($params['phone'])) {
            return json_encode(['code'=>10001, 'msg'=>'手机号不能为空']);
        }
        $customModel = new Customer();
        $has = $customModel->where('mobile_md5', md5($params['phone']))->count();
        if ($has > 0) {
            return json_encode(['code'=>10002, 'msg'=>'已存在']);
        }

        $dict = new SystemDict();
        $citys = array_flip($dict->getListByType($dict::TYPE_CITY)); // 获取城市信息配置
        $customer = $params;
        $cityName = strval(explode('/', $customer['working_city'])[1]);
        $custom = [
            'source' => $this->sourceId,
            'name' => $customer['name'],
            'city' => intval($citys[str_replace("市", "", $cityName)]),
            'mobile' => $customer['phone'],
            'age' => intval($customer['age']),
            'amount' => intval($customer['loan_amount']) * 10000,
            'apply_time' => time(),
            'house' => 0,
            'car' => 0,
            'policy' => 0,
            'wage' => 0,
            'funds' => 1,
            'insurance' => 0,
            'credit' => 0,
            'channel_id' => $customer['order_id'],
            'cost' => $channel->cost,
        ];
        if ($customer['real_estate'] ) {
            if ($customer['real_estate'] == '无房') {
                $custom['house'] = 1;
            } else {
                $custom['house'] = 2;
            }
        }
        if ($customer['car'] ) {
            if ($customer['car'] == '无车') {
                $custom['car'] = 1;
            } else {
                $custom['car'] = 2;
            }
        }
        if ($customer['insurance'] ) {
            if ($customer['insurance'] == '无') {
                $custom['policy'] = 1;
            } else {
                $custom['policy'] = 2;
            }
        }

        if ($customer['reserved_funds']) {
            if ($customer['reserved_funds'] == '无公积金') {
                $custom['funds'] = 1;
            } else {
                $custom['funds'] = 2;
            }
        }
        if ($customer['social_security']) {
            if ($customer['social_security'] == '无社保') {
                    $custom['insurance'] = 1;
            } else {
                $custom['insurance'] = 2;
            }
        }
        if ($customer['credit_record']) {
            if ($customer['credit_record'] == '无逾期') {
                $custom['credit'] = 1;
            } else {
                $custom['credit'] = 2;
            }
        }
        if ($customer['sex']) {
            if ($customer['sex'] == '男') {
                $custom['sex'] = 1;
            } else if ($customer['sex'] == '女') {
                $custom['sex'] = 2;
            }
        }
        $custom['mobile_md5'] = md5($custom['mobile']);
        $custom['mobile_jiami'] = app(CustomerService::class)->encrypt($custom['mobile']);
        unset($custom['mobile']);
        $id = (new Customer())->insertGetId($custom);
        Log::info("IN1686510036 新用户: " . $channel['name'] . ":". $id);
        return json_encode(['code'=>0, 'msg'=>'进件成功']);
    }

    public function decode($data, $token) {
        openssl_private_decrypt(base64_decode($data), $result, $token, OPENSSL_PKCS1_PADDING );
        return $result;
    }
}
