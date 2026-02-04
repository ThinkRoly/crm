<?php

namespace App\Services\Channel;

use Exception; 
use App\Models\Channel;
use App\Models\SystemDict;
use App\Models\Notice;
use App\Models\Customer;
use App\Services\CustomerService;
use JsonException;

class XMsgChannel
{
    public function getData() {
        $retData = []; 
        try {
            $apiUrl = 'http://39.103.179.221:8088/v2callApi.aspx';
            $userId = '5633';
            $account = '深圳刘伟';
            $password = '+pb123456';

            // 初始化客户端
            $client = new SmsApiClient($apiUrl, $userId, $account, $password);
            $client->setTimeout(60);
            
            $data = $client->queryUpstream();
            
	    $customModel = new Customer();
            $retData = $data;
            // 可添加日志记录
	    foreach ($item as $retData){
		$mobile = $item['mobile'];
		$customer = $customModel->where('mobile_md5', md5($mobile))->orderBy('id', 'desc')->get()[0];
		$notice = [
			'custom_id' => $customer['id'],
			'type' => 6,
			'follow_user_id' => $customer['follow_user_id'],
			'remark' => $item['content']
		];
		$id = (new Notice())->insertGetId($notice);
	}
            
        } catch (Exception $e) {
            $retData = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }

        return $retData;
    }
}

// 注意：在命名空间内定义的类，需要放在同一个命名空间下
class SmsApiClient
{
    // 接口配置参数
    private $apiUrl;      // 接口地址
    private $userId;      // 企业ID
    private $account;     // 账号
    private $password;    // 密码
    private $timeout = 30; // 超时时间(秒)

    /**
     * 构造函数：初始化配置
     */
    public function __construct(string $apiUrl, string $userId, string $account, string $password) {
        $this->apiUrl = $apiUrl;
        $this->userId = $userId;
        $this->account = $account;
        $this->password = $password;
    }

    /**
     * 设置超时时间
     */
    public function setTimeout(int $seconds): self {
        $this->timeout = $seconds;
        return $this;
    }

    /**
     * 查询上行回复数据
     */
    public function queryUpstream(string $ownExt = ''): array {
        $timestamp = date('YmdHis');
        $sign = $this->generateSign($timestamp);
        $postData = [
            'userid'    => $this->userId,
            'timestamp' => $timestamp,
            'sign'      => $sign,
            'action'    => 'query',
            'ownExt'    => $ownExt
        ];

        $response = $this->sendPostRequest($postData);
        return $this->parseXmlResponse($response);
    }

    /**
     * 生成签名
     */
    private function generateSign(string $timestamp): string {
        $signStr = $this->account . $this->password . $timestamp;
        return md5($signStr);
    }

    /**
     * 发送POST请求
     */
    private function sendPostRequest(array $postData): string {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->apiUrl,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => false, 
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $errno = curl_errno($ch); // 获取错误代码
        curl_close($ch);

        if ($errno !== 0) {
            throw new Exception("CURL错误[{$errno}]：{$error}");
        }

        if ($response === false) {
            throw new Exception("未获取到响应数据");
        }
	var_dump($response);

        return $response;
    }

    /**
     * 解析XML响应
     */
    private function parseXmlResponse(string $xmlString): array {
        libxml_use_internal_errors(true);
        
        $xml = simplexml_load_string($xmlString);

        if ($xml === false) {
            $errors = [];
            foreach (libxml_get_errors() as $err) {
                $errors[] = "行{$err->line}：{$err->message}";
            }
            // 清除错误缓存
            libxml_clear_errors();
            throw new Exception("XML解析错误：" . implode('; ', $errors));
        }

        $result = [];
        foreach ($xml->callbox as $callbox) {
            $result[] = [
                'mobile'      => (string)$callbox->mobile,
                'taskid'      => (string)$callbox->taskid,
                'content'     => (string)$callbox->content,
                'receivetime' => (string)$callbox->receivetime,
                'extno'       => (string)$callbox->extno
            ];
        }

        return $result;
    }
}

// 测试代码（通常建议放在单独的测试文件中，或通过框架调用）
// 注意：在命名空间内调用时需要注意上下文
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    $xmsg = new XMsgChannel();
    $result = $xmsg->getData();
    var_dump($result);
}
