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
class JhhChannel
{
    public function feedback($star, $mobilemd5, $channelid) {
	if ($star <= 0){ 
            $star = 1;
        } else if ($star == 1) {
            $star = 2;
        } else if ($star == 2) {
            $star = 3;
        } else if ($star >= 3) {
            $star = 4;
        } 
        $client = new Client();

        // 要发送的数据
        $params = [
		'org' => 519,
		'mobileMd' => $mobilemd5, 
		'star'=>$star,
        ];

        // 发送JSON POST请求
        $response = $client->request('POST', 'https://yk.rongchengyanshu.com/fxm/channel/openApi/callStar', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => $params
        ]);

        // 获取并输出响应体
        return $response->getBody()->getContents();
    }
}

