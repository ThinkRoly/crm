<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use App\Models\CustomerRuleConfig;
use App\Services\CustomerService;

class Mobile extends Command
{
    protected $signature = 'mobile';
    protected $description = '手机号重复判断策略';

    /**
     ** 执行控制台命令
     **/
    public function handle() {
        // 获取上次判断到的id
        $id = intval(@file_get_contents("/www/wwwlogs/mobilelog.txt"));
        // 取到所有规则
        $model = new Customer();
        $mobiles = $model->select('id', 'mobile_jiami')->where('id', '>', $id)->orderby('id')->get();
        foreach ($mobiles as $mobile) {
            $id = $mobile->id;
            $x = $model->select('anum')->where('id', '<', $mobile->id)->where('mobile_jiami', $mobile->mobile_jiami)->orderby('id' ,'desc')->take(1)->get()->toArray();
            if ($x && $x[0]) {
                if ($x[0]['anum'] == 0) {
                    $num = $x[0]['anum'] + 2;
                } else {
                    $num = $x[0]['anum'] + 1;
                }
                $model->where('id', $id)->update(['anum' => $num]);
            }
        }
        file_put_contents("/www/wwwlogs/mobilelog.txt", $id);
    }
}
