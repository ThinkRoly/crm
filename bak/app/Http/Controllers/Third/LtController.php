<?php

namespace App\Http\Controllers\Third;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Customer;
use App\Models\CustomerLog;
use App\Models\SystemDict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\CustomerService;
use App\Services\UserService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class LtController extends Controller
{
    private $sourceId = 11;

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
        if (empty($params)) {
            return json_encode(['code'=>1000, 'msg'=>'校验通过']);
        }

        $customModel = new Customer();
        $has = $customModel->where('mobile_md5', $params['phone'])->count();
        if ($has > 0) {
            return json_encode(['code'=>'0000', 'msg'=>'手机号存在', 'businessCode'=> '0001']);
        } else {
            return json_encode(['code'=>'0000', 'msg'=>'手机号不存在', 'businessCode'=> '0000']);
        }
    }

    public function order(Request $request) {
        $voucherCode = "6IHHByqfkH8CZXAD";
        $key = '2f108d88bf7537e6';
        /// $voucherCode = "xf12345554454";
        // $key = 'qweasdzxcqweasdz';
        $params = $request->json()->all();
        if (empty($params)) {
            return json_encode(['code'=>10001, 'msg'=>'订单数据异常']);
        }
        if (empty($params['orderNo'])) {
            return json_encode(['code'=>10001, 'msg'=>'订单号不能为空']);
        }
        $orderNo = $params['orderNo'];

        $client = new Client();

        try {

                // 要发送的JSON数据
                $params = [
                    'voucherCode' => $voucherCode,
                    'orderNo' => $orderNo,
                ];

            // 发送POST请求
            $response = $client->request('POST', 'https://gw.youxinsign.com/rxk-saas/api/organ-clue-api/readClues', [
                'json' => $params, // 自动将数组编码为JSON
                'headers' => [
                    'Content-Type' => 'application/json',
                    // 可以添加更多的HTTP头部信息
                ]
            ]);

            // 获取并输出响应体
            $data = json_decode($response->getBody()->getContents(), true)['data'];

            if (empty($data)) {
                return json_encode(['code'=>10003, 'msg'=>'取数接口异常']);
            }


            $encryptedData = hex2bin($data);
            $data = openssl_decrypt($encryptedData, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
            $data = json_decode($data, true)[0];
            if (empty($data)) {
                return json_encode(['code'=>10004, 'msg'=>'取数接口异常']);
            }
        } catch (RequestException $e) {
            return json_encode(['code'=>10002, 'msg'=>'取数接口异常']);
            // 处理异常情况
        }

        $dict = new SystemDict();
        $citys = array_flip($dict->getListByType($dict::TYPE_CITY)); // 获取城市信息配置
        $customer = $data;
        $custom = [
            'source' => $this->sourceId,
            'name' => $customer['name'],
            'city' => intval($citys[$customer['city']]),
            'mobile' => $customer['phone'],
            'age' => intval($customer['age']),
            'amount' => intval($customer['loanMoney']) * 10000,
            'apply_time' => time(),
            'house' => 0,
            'car' => 0,
            'policy' => 0,
            'wage' => 0,
            'funds' => 0,
            'insurance' => 0,
            'credit' => 0,
            'channel_id' => $customer['orderNo']
        ];
        if (strval($customer['house']) === "0" ) {
                $custom['house'] = 1;
        } else if ($customer['house'] > 0 ) {
                $custom['house'] = 2;
        }
        if (strval($customer['car']) === "0" ) {
                $custom['car'] = 1;
        } else if ($customer['car'] > 0 ) {
                $custom['car'] = 2;
        }
        if (strval($customer['baodanScop']) === "0" ) {
                $custom['policy'] = 1;
        } else if ($customer['baodanScop'] > 0 ) {
                $custom['policy'] = 2;
        }

        if (strval($customer['gjjScop']) === "0" ) {
                $custom['funds'] = 1;
        } else if ($customer['gjjScop'] > 0 ) {
                $custom['funds'] = 2;
        }
        if (strval($customer['sbScop']) === "0" ) {
                $custom['insurance'] = 1;
        } else if ($customer['sbScop'] > 0 ) {
                $custom['insurance'] = 2;
        }
        if (strval($customer['credit']) === "10" ) {
                $custom['credit'] = 1;
        } else if ($customer['credit'] > 10 ) {
                $custom['credit'] = 2;
        }
        if (strval($customer['sex']) === "0" ) {
                $custom['sex'] = 2;
        } else if ($customer['sex'] == 1 ) {
                $custom['sex'] = 1;
        }
        try {
            // 要发送的JSON数据
                $params = [
                    'voucherCode' => $voucherCode,
                    'orderNo' => $orderNo,
                    'status' => 1,
                    'remark' => 0,
                ];


            // 发送POST请求
            $response = $client->request('POST', 'https://gw.youxinsign.com/rxk-saas/api/organ-clue-api/updateClueStatus', [
                'json' => $params, // 自动将数组编码为JSON
                'headers' => [
                    'Content-Type' => 'application/json',
                    // 可以添加更多的HTTP头部信息
                ]
            ]);

            // 获取并输出响应体
            echo  $response->getBody();
        } catch (RequestException $e) {
        }
        $customService = app(CustomerService::class);
        $model = new Customer();
	$custom['mobile_jiami'] = $customService->encrypt($custom['mobile']);
        $custom['mobile_md5'] = md5($custom['mobile']);
        $id = (new Customer())->insertGetId($custom);
        $userService = app(UserService::class);
        $users = $userService->getAllAssignUser();
        Log::info("IN1663039375 新数据分配的所有用户" . json_encode($users));
        foreach ($users as $userId => $leftCount) {
            $userIdStr = $userId;
            $userId = intval($userId);
            if ($leftCount <= 0) {
                continue;
            }
            Log::info("IN1663039375 分配" . $id . ' --> ' . $userId );
            $flag = $customService->assign($id, $userId, 0, CustomerService::ASSIGN_TYPE_NEW);
            Log::info("IN1663039375 分配结果 " . $id . ' --> ' . $userId . " : ".$flag);
            break;
        }
        return json_encode(['code'=>0, 'msg'=>'进件成功']);
    }

    public function decode($data, $token) {
        openssl_private_decrypt(base64_decode($data), $result, $token, OPENSSL_PKCS1_PADDING );
        return $result;
    }
}