<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Support\Facades\Log;

class Channel extends Command
{
    protected $signature = 'channel {--name=}';
    protected $description = '拉取渠道数据';

    /**
     ** 执行控制台命令
     **/
    public function handle() {
        $channelName  = $this->option('name');
        $className = 'App\Services\Channel\\'.ucfirst($channelName).'Channel';
        $channel = new $className();
        $customService = new CustomerService();
        $data = $channel->getData();
        foreach ($data as $item) {
            $hasInfo = Customer::where('source', $item['source'])->where('channel_id', $item['channel_id'])->get()->toArray();
            if (empty($hasInfo)) {
                $item['mobile_md5'] = md5($item['mobile']);
                $item['mobile_jiami'] = $customService->encrypt($item['mobile']);
                unset($item['mobile']);
                $id = (new Customer())->insertGetId($item);
                Log::info("IN1686510036 新用户: " . $channelName . $id);
            }
        }
    }


}
