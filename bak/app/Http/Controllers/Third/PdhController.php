<?php

namespace App\Http\Controllers\Third;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Customer;
use App\Models\SystemDict;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\CustomerService;
use App\Services\UserService;

class PdhController extends Controller
{
    private $sourceId = 3;

    public function checkUser(Request $request) {
        $params = $request->json()->all();
        if (empty($params) || empty($params['mobile'])) {
            return json_encode(['code'=>'000000', 'msg'=>'可以接收']);
        }

        $customModel = new Customer();
        $has = $customModel->where('mobile_md5', $params['mobile'])->count();
        if ($has > 0) {
            return json_encode(['code'=>'999999', 'msg'=>'手机号已存在']);
        } else {
            return json_encode(['code'=>'000000', 'msg'=>'可以接收']);
        }
        return json_encode(['code'=>'000000', 'msg'=>'可以接收']);
    }

}
