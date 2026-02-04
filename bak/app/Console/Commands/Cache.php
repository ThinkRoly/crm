<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomerLog;
use App\Models\SystemUser;

class Cache extends Command
{
    protected $signature = 'cache';
    protected $description = '缓存一波';

    /**
        *      * 执行控制台命令
        *           */
    public function handle()
    {
        $model = new SystemUser();
        $custommodel = new CustomerLog();

         // 开始计算新数据情况
        $list = $model->getLists(['current'=>1, 'pageSize'=>1000]);
        $newlimit = 0;
        $innerlimit = 0;
        $newnow = 0;
        $newnow2 = 0;
        $innernow = 0;
        foreach ($list as $key => $item) {
            $config = json_decode($item['assign_rights'], true);
            $item['inner'] = intval($config['inner']);
            $item['new'] = intval($config['new']);
            $status = $item['status'];
            $online = $item['online'];
            $temp = $custommodel->getTodayNew($item['id']);
            $newnow2 += $temp;
            if ($online == 1 && $status == 1 && $item['new'] > 0) {
                $newlimit += $item['new'];
                $newnow += $temp;
            }
            if ($item['inner'] > 0) {
                $innerlimit += $item['inner'];
                $innernow += $custommodel->getTodayAgain($item['id']);
            }
        }
        $data['newlimit'] = $newlimit;
        $data['innerlimit'] = $innerlimit;
        $data['newnow'] = $newnow;
        $data['newnow2'] = $newnow2;
        $data['innernow'] = $innernow;
        file_put_contents("/www/wwwlogs/limit", json_encode($data));
    } 
}