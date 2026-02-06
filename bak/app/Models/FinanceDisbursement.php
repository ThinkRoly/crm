<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FinanceDisbursement extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finance_disbursement';

    protected $guarded = [];

    public $timestamps = false;


    private function _createWhere($params) {
        $query = $this;
        if (isset($params['customer_name']) && !empty($params['customer_name'])) {
            $query = $query->where('customer_name', $params['customer_name']);
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

    public function getBills($params) {
        $query = DB::table('finance_disbursement')
                ->select(
                    'customer_name',
                    DB::raw('SUM(disbursement_amount) as total_amount'),
                    DB::raw('COUNT(*) as loan_count')
                )
                ->groupBy('customer_name');
        $offset = ($params['current'] - 1) * $params['pageSize'];
        return $query
            ->orderByDesc('total_amount')
            ->offset($offset)
            ->limit($params['pageSize'])
            ->get();
    }

    public function getBillCount($params) {
        $query = DB::table('finance_disbursement')
                ->select(
                    'customer_name',
                    DB::raw('SUM(disbursement_amount) as loan_amount'),
                    DB::raw('COUNT(*) as loan_count')
                )
                ->groupBy('customer_name');
        return $query->count();
    }

    public function getCount($params = []) {
        return $this->_createWhere($params)->count();
    }

    public function details($customer_name) {
        return $this->where('customer_name', $customer_name)->get();
    }



}
