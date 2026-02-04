<?php

namespace App\Services\Channel;

use App\Models\Channel;
use App\Models\SystemDict;
use App\Services\CustomerService;
use GuzzleHttp\Client;
use JsonException;

/**
 * rong360
 */
class CmyjChannel
{
    public function feedback($star, $mobilemd5, $channelid) {
	if ($star <= 0){ 
            $star = 0;
        }
        $client = new Client();

        // 要发送的数据
        $params = [
		'channelCode' => 'hrhP1',
		'phoneMd5' => $mobilemd5, 
		'starLevel'=>$star,
        ];

	var_dump($params);
        // 发送JSON POST请求
        $response = $client->request('POST', 'https://zd.wanrongxin.com/dev-api/open/star/commonStarBack', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => $params
        ]);

	echo json_encode($params) .PHP_EOL;
        // 获取并输出响应体
        echo $response->getBody() . PHP_EOL;
    }
}

