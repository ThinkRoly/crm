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

class LyhController extends Controller
{
    private $sourceId = 12;

    private function getRealData($request) {
        $data = $request->json()->all();
        Log::info("IN1663039376 push参数 lyh: " . json_encode($data));
        $data = base64_decode($data['sign']);
        $data = openssl_decrypt($data, 'aes-128-cbc',
            "nYKL5dyLbZHVSJ2V", OPENSSL_RAW_DATA, "WGcv9U76fXGUfkd3");
        return json_decode($data, true);
    }

    public function order(Request $request) {
        $channel = Channel::find($this->sourceId);
        $params = $this->getRealData($request);
        if (empty($params)) {
            return json_encode(['code'=>202, 'msg'=>'订单数据异常1']);
        }
        if (empty($params['phoneNumber'])) {
            return json_encode(['code'=>202, 'msg'=>'手机号不能为空2']);
        }
        $dict = new SystemDict();
        $citys = array_flip($dict->getListByType($dict::TYPE_CITY)); // 获取城市信息配置
        $customer = $params;
        $custom = [
            'source' => $this->sourceId,
            'name' => strval($customer['name']),
            'city' => intval($citys[$customer['city']]),
            'mobile' => $customer['phoneNumber'],
            'age' => intval($customer['age']),
            'amount' => intval($customer['loanAmount']),
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
        if (isset($customer['house'])) {
            if ($customer['house'] == '1') {
                $custom['house'] = 2;
            } else {
                $custom['house'] = 1;
            }
        }
        if (isset($customer['car'])) {
            if ($customer['car'] == '1') {
                $custom['car'] = 2;
            } else {
                $custom['car'] = 1;
            }
        }
        if (isset($customer['lifeInsurance'] )) {
            if ($customer['lifeInsurance'] == '1') {
                $custom['policy'] = 2;
            } else {
                $custom['policy'] = 1;
            }
        }

        if (isset($customer['houseFund'])) {
            if ($customer['houseFund'] == '1') {
                $custom['funds'] = 2;
            } else {
                $custom['funds'] = 1;
            }
        }
        if (isset($customer['socialInsurance'])) {
            if ($customer['socialInsurance'] == '1') {
                    $custom['insurance'] = 2;
            } else {
                $custom['insurance'] = 1;
            }
        }
        if (isset($customer['sex'])) {
            if ($customer['sex'] == '1') {
                $custom['sex'] = 1;
            } else if ($customer['sex'] == '2') {
                $custom['sex'] = 2;
            }
        }
        $customService = app(CustomerService::class);
	    $custom['mobile_jiami'] = $customService->encrypt($custom['mobile']);
        $custom['mobile_md5'] = md5($custom['mobile']);
        unset($custom['mobile']);
        $id = (new Customer())->insertGetId($custom);
        Log::info("IN1686510036 新用户: " . $channel['name'] . ":". $id);
        return json_encode(['code'=>200, 'msg'=>'接受用户成功']);
    }

    
    public function checkUser(Request $request) {
        $params = $request->json()->all();
        $no = $params['serialNo'];
        $data = base64_decode($params['requestData']);
        $data = json_decode(openssl_decrypt($data, 'aes-128-cbc',
            "nYKL5dyLbZHVSJ2V", OPENSSL_RAW_DATA, "WGcv9U76fXGUfkd3"), true);

        $customModel = new Customer();
        $mobile = $data['encryptValue'];
        if (empty($mobile)) {
            return json_encode(['serialNo'=>$no, 'code'=>0, 'msg'=>'手机号参数为空', 'data'=>[]]);
        }
        $mobile = substr($mobile, 0, 9);
        $mobiles = [];
        for ($i = 0; $i<=99; $i++) {
            $mobiles[] = md5($mobile . str_pad($i, 2, "0", STR_PAD_LEFT));
        }
        $data = $customModel->select('mobile_md5')->whereIn('mobile_md5', $mobiles)->get()->toArray();
        $customModel = new Customer();
        $has = $customModel->where('mobile_md5', $params['phone_md5'])->count();
        if ($has > 0) {
            return json_encode(['serialNo'=>$no, 'code'=>1, 'msg'=>'', 'data'=>[]]);
        } else {
            return json_encode(['serialNo'=>$no, 'code'=>1, 'msg'=>'', 'data'=>array_column($data, 'mobile_md5')]);
        }
    }
}
