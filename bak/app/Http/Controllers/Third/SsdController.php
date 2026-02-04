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

class SsdController extends Controller
{
    private $sourceId = 1;

    private function getRealData($request) {
        $data = json_encode($request->json()->all());
        Log::info("IN1663039375 push参数: " . json_encode($request->json()->all()));
        $channel = Channel::find($this->sourceId);
        $data = $this->rsa_base64_decode($data, $channel->config, $channel->token, 128);
        return $data['data'];
    }

    public function test() {

        $channel = Channel::find($this->sourceId);
        $params = json_decode('{ "f":"apply", "userid": 56326189, "product_id":60014, "name":"测试李四", "idno":"352203199512216319", "age":26, "gender":"男", "phone":"15255780451", "working_city":"北京市/北京市", "monthly_salary":2000, "salary_type":"银行代发", "loan_amount":"15624",
    "loan_period":"12个月", "company_years":"6个月以上", "education":"本科及以上", "gjj":"6个月以上", "shebao":"6个月以上", "credit":"1万-3万", "credit_record":"无逾期", "insurance":"无", "house":"无", "zhima_score":"720", "vehicle":"无", "loan_purpose":"消费" }', true);
        $encrypt = $this->rsa_base64_encode(json_encode($params), $channel->token, $channel->config, 128);
        $encrypt_data = json_decode($encrypt, true);
        $params_data = [
            'sign' => $encrypt_data['sign'],//加签
            'content' => $encrypt_data['content'],//加密
        ];
        $params_data = json_encode($params_data);
        echo "请求参数：" . $params_data . "\n";

        $data = $this->rsa_base64_decode($params_data, $channel->config, $channel->token, 128);
        echo "解密验签：" . json_encode($data);
    }

    public function checkUser(Request $request) {
        $params = $this->getRealData($request);
        if (empty($params)) {
            return json_encode(['code'=>'1000', 'msg'=>'解密失败']);
        }
        if (empty($params['phone_md5'])) {
            return json_encode(['code'=>'1002', 'msg'=>'手机号md5不能为空']);
        }
        $customModel = new Customer();
        $has = $customModel->where('mobile_md5', $params['phone_md5'])->count();
        if ($has > 0) {
                return json_encode(['code'=>'1002', 'msg'=>'手机号已存在']);
        } else {
                return json_encode(['code'=>'0000', 'msg'=>'可以借款']);
        }

    }

    public function queryState(Request $request) {
        $params = $this->getRealData($request);
        if (empty($params)) {
            return json_encode(['code'=>'1000', 'msg'=>'解密失败']);
        }
        if (empty($params['userid'])) {
            return json_encode(['code'=>'1000', 'msg'=>'用户编号不能为空']);
        }
        $custom = Customer::where('source', $this->sourceId)->where('channel_id', $params['userid'])->get()->toArray();
        if ($custom) {
            return json_encode([
                'code'=>'0000',
                'msg'=>'查询成功',
                'apply_success' => true,
                'state_time' => $custom[0]['create_time']
            ]);
        } else {
            return json_encode([
                'code'=>'0000',
                'msg'=>'查询成功',
                'apply_success' => false,
            ]);
        }
    }

    public function apply(Request $request) {
        $channel = Channel::find($this->sourceId);
        $params = $this->getRealData($request);
        if (empty($params)) {
            return json_encode(['code'=>'1000', 'msg'=>'解密失败']);
        }
        if (empty($params['phone'])) {
            return $this->apiReturn(static::ERROR, [], '手机号不能为空');
        }
        if (empty($params['userid'])) {
            return $this->apiReturn(static::ERROR, [], '用户id不能为空');
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
            'amount' => intval($customer['loan_amount']),
            'apply_time' => time(),
            'house' => 0,
            'car' => 0,
            'policy' => 0,
            'wage' => 0,
            'funds' => 1,
            'insurance' => 0,
            'credit' => 0,
            'channel_id' => $customer['userid'],
            'cost' => $channel->cost,
        ];
        if ($customer['house'] && $customer['house'] != '未知') {
            if ($customer['house'] == '无') {
                $custom['house'] = 1;
            } else {
                $custom['house'] = 2;
            }
        }
        if ($customer['vehicle'] && $customer['vehicle'] != '未知') {
            if ($customer['vehicle'] == '无') {
                $custom['car'] = 1;
            } else {
                $custom['car'] = 2;
            }
        }
        if ($customer['insurance'] && $customer['insurance'] != '未知') {
            if ($customer['insurance'] == '无') {
                $custom['policy'] = 1;
            } else {
                $custom['policy'] = 2;
            }
        }

        if ($customer['salary_type'] && $customer['salary_type'] != '未知') {
            $custom['wage'] = 2;
        }
        if ($customer['gjj'] && $customer['gjj'] != '未知') {
            if ($customer['gjj'] == '无本地公积金') {
                    $custom['funds'] = 1;
            } else {
                $custom['funds'] = 2;
            }
        }
        if ($customer['shebao'] && $customer['shebao'] != '未知') {
            if ($customer['shebao'] == '无本地社保') {
                    $custom['insurance'] = 1;
            } else {
                $custom['insurance'] = 2;
            }
        }
        if ($customer['credit_record'] && $customer['credit_record'] != '未知') {
            if ($customer['credit_record'] == '无逾期') {
                $custom['credit'] = 1;
            } else {
                $custom['credit'] = 2;
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
        return json_encode(['code'=>'0000', 'msg'=>'可以借款']);
    }



    function rsa_base64_encode($json_data, $encrypt_key, $sign_key, $key_lenth = 0) {//2048  256    1024 128  对方公钥 己方私钥
        $encrypt_data = $this->rsaEncrypt($json_data, $encrypt_key);//256-11
        $encode_data = base64_encode($encrypt_data);

        $sign = base64_encode($this->rsaSign($encrypt_data, $sign_key));

        $ret = array(
            'content' => $encode_data,
            'sign' => $sign
        );
        return json_encode($ret);
    }


    function rsaEncrypt($rawData, $pubKey) {//合作方公钥
        $res = openssl_get_publickey($pubKey);
        if ($res == false) {
            throw new \Exception('invalid public key');
        }
        $keyInfo = openssl_pkey_get_details($res);
        $step = $keyInfo['bits'] / 8 - 11;
        $encryptedList = array();
        for ($i = 0, $len = strlen($rawData); $i < $len; $i += $step) {
            $data = substr($rawData, $i, $step);
            $encrypted = '';
            openssl_public_encrypt($data, $encrypted, $res);
            $encryptedList[] = ($encrypted);
        }
        openssl_free_key($res);
        $data = join('', $encryptedList);
        return $data;
    }


    function rsaSign($data, $priKey, $sign_alg = OPENSSL_ALGO_SHA1) {//rsa签名

        $res = openssl_get_privatekey($priKey);

        openssl_sign($data, $sign, $res, $sign_alg);

        openssl_free_key($res);

        return $sign;
    }

    function rsa_base64_decode($data, $decrypt_key, $sign_key, $key_lenth = 0) {//己方私钥  对方公钥 2048  256    1024 128
        $data_arr = json_decode($data, true);
        $content = base64_decode($data_arr['content']);
        $decrypt_data = $this->rsaDecrypt($decrypt_key, $content);
        $sign = base64_decode($data_arr['sign']);

        $result = $this->rsaVerify($content, $sign, $sign_key);

        $ret = array();
        $ret['data'] = json_decode($decrypt_data, true);
        $ret['sign'] = $result;

        return $ret;
    }


    function rsaDecrypt($privateKey, $encryptedData) {//我方私钥
        $res = openssl_get_privatekey($privateKey);
        if ($res == false) {
            throw new \Exception('invalid private key');
        }
        $keyInfo = openssl_pkey_get_details($res);
        $step = $keyInfo['bits'] / 8;
        $decryptedList = array();
        for ($i = 0, $len = strlen($encryptedData); $i < $len; $i += $step) {
            $data = substr($encryptedData, $i, $step);
            $decrypted = '';
            openssl_private_decrypt($data, $decrypted, $res);
            $decryptedList[] = $decrypted;
        }
        openssl_free_key($res);
        return join('', $decryptedList);
    }

    function rsaVerify($data, $sign, $pubKey, $sign_alg = OPENSSL_ALGO_SHA1) {//rsa验签

        $res = openssl_get_publickey($pubKey);

        $result = openssl_verify($data, $sign, $res, $sign_alg);

        openssl_free_key($res);

        return $result;
    }


}
