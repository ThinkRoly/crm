<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FinancePaymentPlan extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'finance_payment_plan';

    protected $guarded = [];

    public $timestamps = false;


    private function _createWhere($params) {
        $query = $this;
        if (isset($params['customer_name']) && !empty($params['customer_name'])) {
            $query = $query->where('customer_name', $params['customer_name']);
        }
        return $query;
    }

    public function getLists($params) {
        $offset = ($params['current'] - 1) * $params['pageSize'];
        $list = $this->_createWhere($params)->orderBy("id", "desc")->skip($offset)->take($params['pageSize'])->get();
        return $list;
    }

    public function processRepayment($planId, $repaymentAmount, $repaymentDate) {
        $plan = $this->findOrFail($planId);
        // 检查是否已经还清
        if ($plan->status === 'completed') {
            throw new \Exception('该期账单已还清');
        }
        if(isEmpty($repaymentAmount)){
            $repaymentAmount = $plan['due_amount'];
        }
        if(isEmpty($repaymentAmount)){
            $repaymentDate = $plan['due_date'];
        }
        return DB::transaction(function () use ($plan, $repaymentAmount, $repaymentDate) {
            // 计算新的已还金额
            $newPaidAmount = $plan->paid_amount + $repaymentAmount;
            $remainingAmount = $plan->due_amount - $newPaidAmount;
            // 更新账单状态
            if ($remainingAmount <= 0) {
                $plan->paid_amount = $plan->due_amount;
                $plan->status = 'completed';
                $plan->completion_date = $repaymentDate;
            } else {
                $plan->paid_amount = $newPaidAmount;
                $plan->status = 'partial';
            }
            $plan->last_repayment_date = $repaymentDate;
            $plan->save();
            return $plan;
        });
    }

    public function getAll($params) {
        return $this->_createWhere($params)->orderBy("id", "desc")->get();
    }

    public function getCount($params = []) {
        return $this->_createWhere($params)->count();
    }


}
