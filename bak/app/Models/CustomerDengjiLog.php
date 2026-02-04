<?php
 
namespace App\Models;

use App\Services\CustomerService;
use Illuminate\Database\Eloquent\Model;

class CustomerDengjiLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'customer_dengji_log';

    protected $guarded = [];

    public $timestamps = false;

}
