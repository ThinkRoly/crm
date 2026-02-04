<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Customer;
use App\Models\CustomerLog;
use App\Models\CustomerRemarkLog;
use App\Models\SystemDict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\CustomerService;
use App\Services\UserService;
use GuzzleHttp\Client;

class ApiController extends Controller
{
    /**
     * 生成签名
     * @param array $args
     * @param string $token
     * return string
     */
    private function makeSign(array $args, string $token = 'your token') {
        if (!$args || !$token) return '';
        ksort($args);
        $sign_str = '';
        foreach ($args as $k => $v) {
            if ($v || is_numeric($v)) {
                $sign_str .= $k.$v;
            }
        }
        return strtoupper(md5(md5($sign_str) . $token));
    }

    public function push(Request $request) {
        $params = $request->all();
        Log::info("IN1663039375 push参数: " . json_encode($params));
        $appId = intval($params['app_id'] - 10030);
        $channel = Channel::find($appId);
        if (empty($channel)) {
            return $this->apiReturn(static::ERROR, [], 'app_id无效');
        }
        // $ciphertext = openssl_encrypt(json_encode(["app_id"=>1]), 'AES-128-ECB', 'YuwbOMuuukysFDkP', OPENSSL_RAW_DATA);
        // 解密
        if ($params['encrypt_type'] == 'AES') {
            $params = json_decode(openssl_decrypt(base64_decode($params['data']), 'AES-128-ECB', 'YuwbOMuuukysFDkP', OPENSSL_RAW_DATA), true);
        }
        $token = $channel['token'];
        $sign = $params['sign'];
        unset($params['sign']);
        $customService = new CustomerService();
        $validSign = $this->makeSign($params, $token);
        if ($sign != $validSign) {
            return $this->apiReturn(static::ERROR, [], '验签失败');
        }
        if (empty($params['mobile'])) {
            return $this->apiReturn(static::ERROR, [], 'mobile不能为空');
        }
        if (empty($params['unique_id'])) {
            return $this->apiReturn(static::ERROR, [], 'unique_id不能为空');
        }
        $dict = new SystemDict();
        $citys = array_flip($dict->getListByType($dict::TYPE_CITY)); // 获取城市信息配置
        $custom = [
            'source' => $appId,
            'channel_id' => strval($params['unique_id']),
            'name' => strval($params['name']),
            'city' => intval($citys[$params['city']]),
            'mobile' => strval($params['mobile']),
            'age' => intval($params['age']),
            'amount' => intval($params['amount']),
            'apply_time' => strtotime($params['apply_time']),
            'house' => intval($params['house']),
            'car' =>   intval($params['car']),
            'policy' => intval($params['policy']),
            'wage' => intval($params['wage']),
            'funds' => intval($params['funds']),
            'insurance' => intval($params['insurance']),
            'qualification' => strval($params['qualification']),
            'sex' => intval($params['sex']),
            'work' => intval($params['work']),
            'credit' => intval($params['credit']),
            'cost' => $channel['cost'],
        ];
        $hasInfo = Customer::where('source', $custom['source'])->where('channel_id', $custom['channel_id'])->get()->toArray();
        if (empty($hasInfo)) {
            $custom['mobile_md5'] = md5($custom['mobile']);
            $custom['mobile_jiami'] = $customService->encrypt($custom['mobile']);
            unset($custom['mobile']);
            $id = (new Customer())->insertGetId($custom);
            Log::info("IN1686510036 新用户: " . $channel['name'] . ":". $id);
        }
        return $this->apiReturn(static::OK);
    }

    public function check(Request $request) {
        $params = $request->all();
        if (empty($params['mobile'])) {
                return $this->apiReturn(static::ERROR, [], "手机号md5不能为空");
        }
        $customModel = new Customer();
	Log::info('IN-CHECK-REPEAT-11');
        $has = $customModel->where('mobile_md5', $params['mobile'])->count();
        if ($has > 0) {
            $today = date('Y-m-d');
            $has_today = $customModel->where('mobile_md5', $params['mobile'])->whereDate('create_time', $today)->count();
            if($has_today == 0){
	        $customer = $customModel->where('mobile_md5', $params['mobile'])->orderBy('id', 'desc')->get()[0];
                Log::info('IN-CHECK-REPEAT');
                $customService = new CustomerService();
                $custom = [
                   'source' => 6,
                   'channel_id' => $customer['channel_id'],
                    'name' => $customer['name'],
                    'city' => $customer['city'],
                    'mobile' => $customer['mobile'],
                    'mobile_jiami' => $customer['mobile_jiami'],
                    'age' => $customer['age'],
                'amount' => $customer['amount'],
                'apply_time' => $customer['apply_time'],
                'house' => $customer['house'],
                'car' =>   $customer['car'],
                'policy' => $customer['policy'],
                'wage' => $customer['wage'],
                'funds' => $customer['funds'],
                'insurance' => $customer['insurance'],
                'qualification' => $customer['qualification'],
                'sex' => $customer['sex'],
                'work' => $customer['work'],
                'credit' => $customer['credit'],
                'cost' => 0,
                'mobile_md5' => $customer['mobile_md5'],
            ];

                $id = (new Customer())->insertGetId($custom);
	        Log::info("激活 新用户: ". $id);
	    }
            return $this->apiReturn(static::ERROR, [], "手机号已存在");
        } else {
                #if ($params['buxuyaozaidiaoyong'] != 100) {
                if (false) {
                    $client = new Client();
                    $options = [
                        'mobile' => $params['mobile'],
                        'buxuyaozaidiaoyong' => 100,
                    ]; 
                    $res = $client->request('POST', 'http://120.78.191.157/api/customer/check', ['form_params' => $options]);
                    return $res->getBody()->getContents();
                }
                return $this->apiReturn(static::OK, [], "未命中");
        }

    }
    public function check8(Request $request) {
        $params = $request->all();
        $customModel = new Customer();
        $mobile = $params['mobile'];
        $appId = intval($params['app_id'] - 10030);
        $channel = Channel::find($appId);
        if (empty($channel)) {
            return json_encode(['code'=>500, 'msg'=>'APPID错误', 'data'=>[]]);
        }
        $token = $channel['token'];
        $mobile = openssl_decrypt(base64_decode($mobile), 'AES-128-ECB', substr($token, 0, 16), OPENSSL_RAW_DATA);
        if (empty($mobile)) {
            return json_encode(['code'=>500, 'msg'=>'手机号解密失败', 'data'=>[]]);
        }
        $mobiles = [];
        for ($i = 0; $i<999; $i++) {
            $mobiles[] = md5($mobile . str_pad($i, 3, "0", STR_PAD_LEFT));
        }
        $data = $customModel->select('mobile_md5')->whereIn('mobile_md5', $mobiles)->get()->toArray();
        return json_encode(['code'=>200, 'msg'=>'', 'data'=>array_column($data, 'mobile_md5')]);
    }

    public function check_r360(Request $request)
    {
        $params = $request->all();
        $customModel = new Customer();
        $mobile = $this->decrypt_360($params['mobile']);
        Log::info("R3 撞库: " . json_encode($mobile));
        if (empty($mobile)) {
            return json_encode(['code' => 0, 'msg' => '', 'data' => []]);
        }
        $mobile = substr($mobile, 0 , 7);
	$mobiles = [];
        for ($i = 0; $i<9999; $i++) {
            $mobiles[] = md5($mobile . str_pad($i, 4, "0", STR_PAD_LEFT));
        }
        $data = $customModel->select('mobile_md5')->whereIn('mobile_md5', $mobiles)->get()->toArray();
	return json_encode(['code'=>200, 'msg'=>'', 'data'=>array_column($data, 'mobile_md5')]);
    }

    public function decrypt_360($encryptData)
    {
        $data = openssl_decrypt($encryptData, 'aes-128-cbc', "QOxyc7gaOjxMGcdQ", OPENSSL_ZERO_PADDING, "y2Z6PDBVYGmi1bGJ");
        return $data;
    }
}
