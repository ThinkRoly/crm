<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CustomerRuleConfig;
use App\Models\Customer;
use App\Services\CustomerService;
use App\Services\UserService;

class Feedback extends Command
{
    protected $signature = 'feedback {--name=}';
    protected $description = '拉取渠道数据';

    /**
     ** 执行控制台命令
     **/
    public function handle() {
        $channelName  = $this->option('name');
        if ($channelName == "yb") {
                $channelId = 7;
        } else if ($channelName == "slf") {
                $channelId = 8;
        } else if ($channelName == "yxh") {
                $channelId = 13;
        } else if ($channelName == "r360") {
                $channelId = 19;
        } else if ($channelName == "jhh") {
                $channelId = 5;
        } else if ($channelName == "cmyj") {
                $channelId = 15;
        } else if ($channelName == "jry") {
                $channelId = 16;
        } else if ($channelName == "lx") {
                $channelId = 18;
        } else {
                return;
        }
        $data = Customer::select('channel_id', 'mobile_md5', 'source', 'star')->where('star', '!=', 0)->where('create_time', '>', date('Y-m-d', strtotime('-3 day')))->where('source', $channelId)->get()->toArray();
        $className = 'App\Services\Channel\\'.ucfirst($channelName).'Channel';
        $channel = new $className();

        foreach ($data as $item) {
            $re = $channel->feedback($item['star'], $item['mobile_md5'], $item['channel_id']) ;
            var_dump($re);
        }
    }


}

