<?php

namespace App\Services\Channel;

use GuzzleHttp\Client;

/**
 * rong360
 */
class CytChannel
{
	public function feedback($star, $mobilemd5, $channelid) {
		if ($star == -1) {
			$star = 2;
		} else if ($star == 1) {
			$star = 1;
		} else if ($star == 2) {
			$star = 1;
		} else {
			$star = 5;
		}
		$client = new Client();

		// 要发送的数据
		$data = [
		    'orgId' => '1265',
		    'dataStandard' => $star,
		    'phoneMd5'=> $mobilemd5
		];
		
		// 发送JSON POST请求
		$response = $client->request('POST', 'https://grapi.grjianr.com/liutu-api/out/api/update/standard', [
		    'headers' => [
		        'Content-Type' => 'application/json',
		    ],
		    'json' => $data
		]);
		
		echo json_encode($data) .PHP_EOL; 
		// 获取并输出响应体
		echo $response->getBody() . PHP_EOL;
	}
}

