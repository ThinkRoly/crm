<?php

namespace App\Services\Channel;

use App\Models\Channel;
use App\Models\SystemDict;
use App\Services\CustomerService;
use JsonException;
use GuzzleHttp\Client;

/**
 * rong360
 */
class LxChannel
{
        public function feedback($star, $mobilemd5, $channelid) {
		if ($star < 0){ $star = 0;}
                $client = new Client();

                // 要发送的数据
                $data = json_encode([
			'applyId' => $channelid,
			'phoneMd5' => $mobilemd5,
			'dealStatus'=> '0000',
			'starLevel'=>$star,
                ]);
		var_dump($data);

		$data =  openssl_encrypt($data, 'des-ede3-cbc','32e326b5053bc97b895f2c14c228af82', 0, '01234567');

		$params = [
			'merchantNo' => 'H4204604453161',
			'timestamp' => date('Y-m-d H:i:s'),
			'reqId' => time() . substr($mobilemd5, 0, 10) . rand(100, 999),
			'data' => $data,
		];
		var_dump($params);
                // 发送JSON POST请求
                $response = $client->request('POST', 'https://fin.lvxtech.com/mis-h5api/api/callback/normal', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $params
                ]);


		echo json_encode($data) .PHP_EOL;
                // 获取并输出响应体
	        echo $response->getBody() . PHP_EOL;
	}
}

