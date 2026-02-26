<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FinanceApplication extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finance_application';

    protected $guarded = [];

    public $timestamps = false;


    private function _createWhere($params) {
        $query = $this;
        if (isset($params['customer_name']) && !empty($params['customer_name'])) {
            $query = $query->where('customer_name', $params['customer_name']);
        }
        if (isset($params['channel']) && !empty($params['channel'])) {
            $query = $query->where('channel', $params['channel']);
        }
        if (isset($params['salesperson']) && !empty($params['salesperson'])) {
            $query = $query->where('salesperson', $params['salesperson']);
        }
        if (isset($params['sign_date']) && !empty($params['sign_date'])) {
            $query = $query->where('sign_date', $params['sign_date']);
        }
        if (isset($params['repayment_date']) && !empty($params['repayment_date'])) {
            $query = $query->where('repayment_date', $params['repayment_date']);
        }
        $query = $query->where('is_del', 0);
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
