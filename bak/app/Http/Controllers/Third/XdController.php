<?php

namespace App\Http\Controllers\Third;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Customer;
use App\Models\SystemDict;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XdController extends Controller
{
    
    private $sourceId = 17;

    public function checkUser(Request $request) {
        $params = $request->all();
        $customModel = new Customer();
        $mobile = $this->decrypt($params['mobile']);
        if (empty($mobile)) {
            return json_encode(['code'=>0, 'msg'=>'', 'data'=>[]]);
        }
        $mobiles = [];
        for ($i = 0; $i<999; $i++) {
            $mobiles[] = md5($mobile . str_pad($i, 3, "0", STR_PAD_LEFT));
        }
        $data = $customModel->select('mobile_md5')->whereIn('mobile_md5', $mobiles)->get()->toArray();
        if ($data) {
            $code = 1;
        } else {
            $code = 0;
        }
        return json_encode(['code'=>$code, 'msg'=>'', 'data'=>array_column($data, 'mobile_md5')]);
    }

    
    private function getRealData($request) {
        $data = $request->json()->all();
        Log::info("IN1663039375 push参数: " . json_encode($request->json()->all()));
        return json_decode($this->decrypt($data['data']), true);
    }

    
    public function apply(Request $request) {
        $channel = Channel::find($this->sourceId);
        $params = $this->getRealData($request);
        if (empty($params)) {
            return json_encode(['code'=>'-14', 'msg'=>'解密失败']);
        }
        if (empty($params['phone'])) {
            return $this->apiReturn(-14, [], '手机号不能为空');
        }
        $dict = new SystemDict();
        $citys = array_flip($dict->getListByType($dict::TYPE_CITY)); // 获取城市信息配置
        $customer = $params;
        $cityName = $customer['city'];
        $custom = [
            'source' => $this->sourceId,
            'name' => $customer['realName'],
            'city' => intval($citys[str_replace("市", "", $cityName)]),
            'mobile' => $customer['phone'],
            'age' => intval($customer['age']),
            'amount' => intval($customer['price']),
            'apply_time' => time(),
            'house' => 0,
            'car' => 0,
            'policy' => 0,
            'wage' => 0,
            'funds' => 1,
            'insurance' => 0,
            'credit' => 0,
            'channel_id' => $customer['phone'],
            'cost' => $channel->cost,
        ];
        if (strval($customer['house']) !== "") {
            if ($customer['house'] == '0') {
                $custom['house'] = 1;
            } else if ($customer['house'] == '1') {
                $custom['house'] = 2;
            }
        }
        if (strval($customer['car']) !== "") {
            if ($customer['car'] == '0') {
                $custom['car'] = 1;
            } else if ($customer['car'] == '1') {
                $custom['car'] = 2;
            }
        }
        if (strval($customer['insurance']) !== "") {
            if ($customer['insurance'] == '0') {
                $custom['policy'] = 1;
            } else if ($customer['insurance'] == '1') {
                $custom['policy'] = 2;
            }
        }
        if (strval($customer['fund']) !== "") {
            if ($customer['fund'] == '0') {
                    $custom['funds'] = 1;
            } else if ($customer['fund'] == '1') {
                $custom['funds'] = 2;
            }
        }
        if (strval($customer['socital']) !== "") {
            if ($customer['socital'] == '0') {
                    $custom['insurance'] = 1;
            } else if ($customer['socital'] == '1') {
                $custom['insurance'] = 2;
            }
        }
        $hasInfo = Customer::where('source', $custom['source'])->where('channel_id', $custom['channel_id'])->get()->toArray();
        if (empty($hasInfo)) {
            $custom['mobile_md5'] = md5($custom['mobile']);
            $custom['mobile_jiami'] = app(CustomerService::class)->encrypt($custom['mobile']);
            unset($custom['mobile']);
            $id = (new Customer())->insertGetId($custom);
            Log::info("IN1686510036 新用户: " . $channel['name'] . ":". $id);
        }
        return json_encode(['code'=>'0', 'msg'=>'可以借款']);
    }

    /**
     * @param string $encryptData
     * @return string
     * @desc aes-128解密
     */
    private function decrypt(string $encryptData): string {
        $data = openssl_decrypt(base64_decode($encryptData), 'aes-128-cbc',
            "BV667P5VR93QN46E", OPENSSL_RAW_DATA, "M879E0BEM01Z7B97");
        return $data;
    }

}
