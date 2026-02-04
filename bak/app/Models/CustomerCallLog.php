<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;

class CustomerCallLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer_call_log';

    protected $guarded = [];

    public $timestamps = false;

    public function saveLog($logId, $customerId, $userId) {
        return $this->insertGetId([
            'log_id' => $logId,
            'customer_id' => $customerId,
            'user_id' => $userId,
        ]);
    }

    public function updateStatus($id, $status, $totalDuration, $file) {
        $this->where('id', $id)->update(['status'=>$status, 'total_duration'=>$totalDuration, 'file'=>$file]);
    }

    

    public function getLogListByCustomerId($customerId, $params) {
        $offset = ($params['current'] - 1) * $params['pageSize'];
        $list = $this->where('customer_id', $customerId)->where('status', '>', -1)->orderBy("id", "desc")->skip($offset)->take($params['pageSize'])->get();
        return $list;
    }

    public function getCountByCustomerId($customerId) {
        return $this->where('customer_id', $customerId)->where('status', '>', -1)->count();
    }
}
