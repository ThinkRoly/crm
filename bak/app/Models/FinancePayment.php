<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FinancePayment extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finance_payment';

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
        if (isset($params['city']) && !empty($params['city'])) {
            $query = $query->where('city', $params['city']);
        }
        if (isset($params['repayment_type']) && !empty($params['repayment_type'])) {
            $query = $query->where('repayment_type', $params['repayment_type']);
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
