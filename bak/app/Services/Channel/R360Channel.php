<?php

namespace App\Services\Channel;

use App\Models\Channel;
use App\Models\SystemDict;
use App\Services\CustomerService;
use JsonException;

/**
 * rong360
 */
class R360Channel
{

    public function feedback($star, $mobile, $id) {

        $channel = Channel::find(19);
        $config = json_decode($channel['config'], true);

        //融360分配的appid
        $appid = $config['appid'];
        //融360分配的密钥
        $prikey = $channel['token'];
        //融360分配的总账号ID
        $uid = $config['uid'];

        if ($star < 1) {
            $star = 1;
        } else if ($star < 5) {
            $star += 1 ;
        } else {
            $star = 5;
        }

        $sdk = new R360OpenapiSdk($appid, $prikey, false);
        $sdk->setMethod('bd.api.common.order.orderfeedbackbeta');
        $sdk->setField("uid", $uid);
        $sdk->setField("order_id", $id);
        $sdk->setField("quality", $star."星");
        return $sdk->execute();
    }

    public function getData() {

        $channel = Channel::find(19);
        $config = json_decode($channel['config'], true);
        
        //融360分配的appid
        $appid = $config['appid'];
        //融360分配的密钥
        $prikey = $channel['token'];
        //融360分配的总账号ID
        $uid = $config['uid'];
        $stime = date("Y-m-d H:i:s", strtotime("-2 Minute"));
        $etime = date("Y-m-d H:i:s");
        
        $sdk = new R360OpenapiSdk($appid, $prikey, false);
        $sdk->setMethod('bd.api.common.order.getorderlist');
        $sdk->setField("uid", $uid);
        $sdk->setField("begin_time", $stime);
        $sdk->setField("end_time", $etime);
        $ret= $sdk->execute();
        $data = $ret['bd_api_common_order_getorderlist_response']['data'];
        $retData = [];
        $dict = new SystemDict();
        $citys = array_flip($dict->getListByType($dict::TYPE_CITY)); // 获取城市信息配置
        echo "抓取时间" . $stime  . ' - ' . $etime . ":";
        echo "抓取结果" . json_encode($ret) . PHP_EOL;
        if (empty($data)) {
            return $retData;
        }
        foreach ($data as $item) {
            $custom = [
                'source' => 19,
                'channel_id' => $item['id'],
                'name' => $item['user_name'],
                'city' => intval($citys[$item['city']]),
                'mobile' => $item['user_mobile'],
                'age' => intval($item['qsearch']['年龄']),
                'amount' => intval($item['application_limit'] * 10000),
                'apply_time' => strtotime($item['create_time']),
                'qualification' => strval($item['apply_from']),
                'house' => 0,
                'car' => 0,
                'policy' => 0,
                'wage' => 0,
                'funds' => 1,
                'insurance' => 0,
                'credit' => 0,
                'cost' => $channel['cost'],
            ];
            if ($item['qsearch']['房产信息']) {
                if ($item['qsearch']['房产信息'] == '无' || $item['qsearch']['房产信息'] == '自建或小产权') {
                    $custom['house'] = 1;
                } else {
                    $custom['house'] = 2;
                }
            }
            if ($item['qsearch']['车辆信息']) {
                if ($item['qsearch']['车辆信息'] == '无') {
                    $custom['car'] = 1;
                } else {
                    $custom['car'] = 2;
                }
            }
            if ($item['qsearch']['保单信息']) {
                if ($item['qsearch']['保单信息'] == '无保单') {
                    $custom['policy'] = 1;
                } else {
                    $custom['policy'] = 2;
                }
            }
            if ($item['qsearch']['打卡工资']) {
                if ($item['qsearch']['打卡工资'] == '无打卡工资') {
                    $custom['wage'] = 1;
                } else {
                    $custom['wage'] = 2;
                }
            }
            if ($item['qsearch']['本地公积金']) {
                if ($item['qsearch']['本地公积金'] == '无本地公积金') {
                    $custom['funds'] = 1;
                } else {
                    $custom['funds'] = 2;
                }
            }
            if ($item['qsearch']['本地社保']) {
                if ($item['qsearch']['本地社保'] == '无本地社保') {
                    $custom['insurance'] = 1;
                } else {
                    $custom['insurance'] = 2;
                }
            }
            if ($item['qsearch']['信用情况']) {
                if ($item['qsearch']['信用情况'] == '无逾期') {
                    $custom['credit'] = 1;
                } else {
                    $custom['credit'] = 2;
                }
            }
            $retData[] = $custom;
        }

        return $retData;
    }
}

class R360OpenapiSdk
{
	protected $appId;
	protected $orgPrivateKey;

	protected $rong360Url;
	protected $_toBeSigned  = null;
	protected $_postData    = null;

    protected $_bizData = null;
    protected $_method  = null;

    public function setField($key, $value)
    {
         $this->_bizData[$key] = $value;
    }

    public function setMethod($method)
    {
         $this->_method = $method;
    }

    public function  execute()
    {
         return $this->sendRequest($this->_bizData, $this->_method);
    }


   /**
     * @param appid
     * @param orgPrivateKey 机构私有key
     * @param test 是否测试环境
     */
	public function __construct($appId, $orgPrivateKey, $test = false)
    {
        $this->rong360Url = 'https://dopen.rong360.com/gateway';
        $this->appId = $appId;
        $this->orgPrivateKey = $orgPrivateKey;
        if ($test) {
            $this->rong360Url = 'https://openapi-test-daikuan.rong360.com/gateway';
        }
    }

    public function sendRequest($bizData, $method)
	{/*{{{*/
        $params = array(
            'method'        => $method,
            'app_id'	=> $this->appId,
            'version'	=> '1.0',
            'sign_type'	=> 'RSA',
            'format'	=> 'json',
            'timestamp'	=> time()
        );
        $params['biz_data'] = json_encode($bizData);

        $this->_toBeSigned = $this->getSignContent($params);
        $params['sign'] = $this->sign($this->_toBeSigned);
        $resp = $this->_crulPost($params, $this->rong360Url);
        return ($resp);
	}/*}}}*/

    protected function getSignContent($params)
    {/*{{{*/
    	ksort($params);

    	$i = 0;
    	$stringToBeSigned = "";
    	foreach ($params as $k => $v) {
    		if ($i == 0) {
    			$stringToBeSigned .= "$k" . "=" . "$v";
    		} else {
    			$stringToBeSigned .= "&" . "$k" . "=" . "$v";
    		}
    		$i++;
    	}
    	unset ($k, $v);
    	return $stringToBeSigned;
    }/*}}}*/

    protected function sign($data)
    {/*{{{*/
    	$res = $this->orgPrivateKey;
    	$ret = openssl_sign($data, $sign, $res);
    	$sign = base64_encode($sign);
    	return $sign;
    }/*}}}*/

    private function _crulPost($postData, $url='')
    {
/*{{{*/
    	if(empty($url)){
    		return false;
    	}

        try
        {
            $this->_postData = http_build_query($postData);
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->_postData);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSLVERSION, 1);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            $res = curl_exec($curl);

            $errno = curl_errno($curl);
            $curlInfo = curl_getinfo($curl);
            $errInfo = 'curl_err_detail: ' . curl_error($curl);
            $errInfo .= ' curlInfo:'. json_encode($curlInfo);

            $arrRet = json_decode($res, true);
            //统一记录日志
            $logLevel = 'info';
            if(!is_array($arrRet) || $arrRet['error']!=200) {
                $logLevel = 'warning';
            }
            curl_close($curl);
        }catch(\Exception $e) {
            //print_r($e->getMessage());
        }

    	return $arrRet;
/*}}}*/
    }

    public function curlPost($postData, $url = '', $header=[],$setTimeOut = 20){
/*{{{*/
        if (empty($url)) {
            return false;
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, $setTimeOut);

        if($header){
            curl_setopt($curl,CURLOPT_HTTPHEADER,$header);
        }

        $res = curl_exec($curl);
        $errno = curl_errno($curl);
        $curlInfo = curl_getinfo($curl);
        $errInfo = 'curl_err_detail: ' . curl_error($curl);
        $errInfo .= ' curlInfo:' . json_encode($curlInfo);
        $arrRet = json_decode($res,true);
        //统一记录日志
        if (!is_array($arrRet)) {
            $logLevel = 'error';
        }
        curl_close($curl);
        return $arrRet;
/*}}}*/
    }
}

