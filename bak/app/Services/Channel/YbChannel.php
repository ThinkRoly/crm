<?php 

namespace App\Services\Channel;

use GuzzleHttp\Client;

/**
 * rong360
 */
class YbChannel 
{
	public function feedback($star, $mobilemd5, $channelid) {
		$dataStandard = 0;
		if ($star < 0) {
			$dataStandard  = 2;
		} else if ($star <= 5) {
			if($star == 1){
				$dataStandard =1;
			}else if ($star == 2){
				$dataStandard =4;
                        }else {
                                $dataStandard =5;
                        }
		} else {
			return;
		}
		$client = new Client(); 

		// 要发送的数据
		$data = [
			"orgId" => 102123,
			"phoneMd5" => $mobilemd5,
			"dataStandard" => $dataStandard,
		];
		
		// 发送JSON POST请求
		$response = $client->request('POST', 'https://wqbapi.veredloanweb.com/xiaowei-api//out/api/update/standard', [
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

