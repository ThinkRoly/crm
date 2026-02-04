<?php 

namespace App\Services\Channel;

use GuzzleHttp\Client;

/**
 * rong360
 */
class SlfChannel 
{
	public function feedback($star, $mobilemd5, $channelid) {
		$dataStandard = 1; 
		if ($star < 0) {
			$dataStandard = 1;
		} else if ($star <= 5) {
			if($star == 1){
				$dataStandard = 2;
			}else if ($star == 2){
				$dataStandard = 2;
                        }else if ($star == 3){
                                $dataStandard = 3;
			} else {
                                $dataStandard =4;
                        }
		} else {
			return;
		}
		$client = new Client(); 

		// 要发送的数据
		$data = [
			"orgId" => "huronghui",
			"phoneMd5" => $mobilemd5,
			"star" => $dataStandard,
		];
		
		// 发送JSON POST请求
		$response = $client->request('POST', 'https://hf.cfqloa.com/api/user/star/add', [
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

