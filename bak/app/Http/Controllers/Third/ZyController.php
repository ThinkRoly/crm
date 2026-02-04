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

class ZyController extends Controller
{
    private $sourceId = 3;

    private function getRealData($request) {
        $data = $request->json()->all();
        Log::info("IN1663039376 push参数: " . json_encode($request->json()->all()));
        return $data;
    }

    public function order(Request $request) {
        $channel = Channel::find($this->sourceId);
        $params = $this->getRealData($request);
        if (empty($params)) {
            return json_encode(['code'=>202, 'msg'=>'订单数据异常1']);
        }
        if (empty($params['mobile'])) {
            return json_encode(['code'=>202, 'msg'=>'手机号不能为空2']);
        }
        $dict = new SystemDict();
        $citys = array_flip($dict->getListByType($dict::TYPE_CITY)); // 获取城市信息配置
        $customer = $params;
        $custom = [
            'source' => $this->sourceId,
            'name' => strval($customer['username']),
            'city' => intval($citys[$customer['city']]),
            'mobile' => $customer['mobile'],
            'age' => 0,
            'amount' => intval($customer['money']) * 10000,
            'apply_time' => time(),
            'house' => 0,
            'car' => 0,
            'policy' => 0,
            'wage' => 0,
            'funds' => 0,
            'insurance' => 0,
            'credit' => 0,
            'channel_id' => 0,
            'cost' => $channel->cost,
        ];
        if ($customer['house'] ) {
            if ($customer['house'] == '无') {
                $custom['house'] = 1;
            } else {
                $custom['house'] = 2;
            }
        }
        if ($customer['car'] ) {
            if ($customer['car'] == '无') {
                $custom['car'] = 1;
            } else {
                $custom['car'] = 2;
            }
        }
        if ($customer['baodan_is'] ) {
            if ($customer['baodan_is'] == '无') {
                $custom['policy'] = 1;
            } else {
                $custom['policy'] = 2;
            }
        }

        if ($customer['gongjijin']) {
            if ($customer['gongjijin'] == '无') {
                $custom['funds'] = 1;
            } else {
                $custom['funds'] = 2;
            }
        }
        if ($customer['shebao']) {
            if ($customer['shebao'] == '无') {
                    $custom['insurance'] = 1;
            } else {
                $custom['insurance'] = 2;
            }
        }
        if ($customer['custGender']) {
            if ($customer['custGender'] == '男') {
                $custom['sex'] = 1;
            } else if ($customer['custGender'] == '女') {
                $custom['sex'] = 2;
            }
        }
        $custom['mobile_md5'] = md5($custom['mobile']);
        $id = (new Customer())->insertGetId($custom);
        Log::info("IN1686510036 新用户: " . $channel['name'] . ":". $id);
        return json_encode(['code'=>200, 'msg'=>'接受用户成功']);
    }

}
