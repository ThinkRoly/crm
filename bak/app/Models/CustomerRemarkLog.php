<?php
 
namespace App\Models;

use App\Services\CustomerService;
use Illuminate\Database\Eloquent\Model;

class CustomerRemarkLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer_remark_log';

    protected $guarded = [];

    public $timestamps = false;

    public function saveLog($id, $remark, $userId) {
        $this->customer_id = $id;
        $this->remark = $remark;
        $this->user_id = $userId;
        return $this->save();
    }


    public function getLogListByCustomerId($customerId, $limit) {
        $userModel = new SystemUser();
        $followUserList = $userModel->getAllUserWithDel();
        $followUserArray = ($followUserList)->mapWithKeys(function ($item) {
            return [$item['id'] => $item['name']];
        })->toArray();
        $list = $this->where('customer_id', $customerId)->orderBy("id", "desc")->take($limit)->get();
        foreach ($list as $key => $item) {
            $item['username'] = $item['user_id'] ? $followUserArray[$item['user_id']] : '系统';
            $list[$key] = $item;
        }
        return $list;
    }

}
