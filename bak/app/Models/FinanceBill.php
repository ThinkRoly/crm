<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FinanceBill extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finance_bill';

    protected $guarded = [];

    public $timestamps = false;


    private function _createWhere($params) {
        $query = $this;
        if (isset($params['userId']) && !empty($params['userId'])) {
            $query = $query->where('follow_user_id', $params['userId']);
        }
        if (isset($params['name']) && !empty($params['name'])) {
            $customer = Customer::where('name', $params['name'])->get()->toArray()[0];
            if ($customer && $customer['id']) {
                $query = $query->where('custom_id', $customer['id']);
            } else {
                $query = $query->where('custom_id', 999999999999);
            }
        }
        if (isset($params['time']) && !empty($params['time']) && is_array($params['time']) ) {
            $query = $query->where('date',  '>', strtotime($params['time'][0]));
            $query = $query->where('date',  '<', strtotime($params['time'][1]));
        }
        return $query;
    }

    public function getLists($params) {
        $offset = ($params['current'] - 1) * $params['pageSize'];
        $list = $this->_createWhere($params)->orderBy("id", "desc")->skip($offset)->take($params['pageSize'])->get();
        return $list;
    }

    public function getCount($params = []) {
        return $this->_createWhere($params)->count();
    }


}
