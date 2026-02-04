<?php

namespace App\Http\Controllers\Third;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class R360Controller extends Controller
{
    
    public function checkUser(Request $request) {
        $params = $request->all();
        $customModel = new Customer();
        $mobile = $this->decrypt($params['mobile']);
        if (empty($mobile)) {
            return json_encode(['code'=>0, 'msg'=>'', 'data'=>[]]);
        }
        $mobiles = [];
        for ($i = 0; $i<999; $i++) {
            $mobiles[] = md5($mobile . str_pad($i, 3, "0", STR_PAD_LEFT));
        }
        $data = $customModel->select('mobile_md5')->whereIn('mobile_md5', $mobiles)->get()->toArray();
        if ($data) {
            $code = 1;
        } else {
            $code = 0;
        }
        return json_encode(['code'=>$code, 'msg'=>'', 'data'=>array_column($data, 'mobile_md5')]);
    }



    /**
     * @param string $encryptData
     * @return string
     * @desc aes-128解密
     */
    private function decrypt(string $encryptData): string {
        $data = openssl_decrypt($encryptData, 'aes-128-cbc',
            "D4geOrYpYvXAPp2i", OPENSSL_ZERO_PADDING, "J4XVhFDND8VONdpG");
        $data = substr($data, 0, strpos($data, "\0"));

        return $data;
    }

}
