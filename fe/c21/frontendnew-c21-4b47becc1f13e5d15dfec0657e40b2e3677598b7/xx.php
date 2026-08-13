<?php


namespace app\customer\controller;

use controller\BasicAdmin;
use PHPExcel;
use PHPExcel_IOFactory;
use service\DataService;
use service\ToolsService;
use think\Cache;
use think\Db;
use think\Log;
use think\Request;

/**
 * 系统权限管理控制器
 * Class Auth
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 18:13
 */
class Customer extends BasicAdmin
{

    /**
     * 默认数据模型
     * @var string
     */
    public $table = 'CrmCustomer';
    public $department_list = [];

    public function index9()
    {
        $this->title = '待调度客户管理';
        $user = session('user');
        $department_id = $user['department_id'];

        if ($department_id == 1) {
            $db = Db::name($this->table)->where('status', 15)->order('create_time desc');
        } else {
            $db = Db::name($this->table)->where('status', 15)->order('create_time desc');
        }


        $get = $this->request->get();
        foreach (['city'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $value = trim($get[$key]);
                $db->where($key, 'like', "%$value%");
            }
        }
        if (isset($get['customer_content']) && $get['customer_content'] !== '') {
            $customer_content = $get['customer_content'];

            $db->where($get['customer_condition'], 'like', "%$customer_content%");

        }
        if (isset($get['condition']) && $get['condition'] !== '') {
            $condition = $get['condition'];

            $db->where($get['loan_conditions'], $condition);

        }
        if (isset($get['remark_time']) && $get['remark_time'] !== '') {
            $day = $get['remark_time'];
            $start = date("Y-m-d", strtotime("-$day day")) . ' 00:00:00';
            $db->where('remark_time', '<', $start);

        }
        if (isset($get['time_content']) && $get['time_content'] !== '') {
            $condition = $get['time_content'];

            if ($condition == '1') {
                $start = date("Y/m/d") . ' 00:00:00';
                $db->where($get['time_type'], '>=', $start);
            } elseif ($condition == '-1') {
                $start = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';
                $end = date("Y-m-d", strtotime("-1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '7') {
                $start = date("Y-m-d", strtotime("-7 day")) . ' 00:00:00';
                $end = date("Y/m/d") . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '30') {
                $start = date('Y-m-01', strtotime(date("Y-m-d"))) . ' 00:00:00';
                $end = date('Y-m-d', strtotime("$start +1 month -1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '-30') {
                $start = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month')) . ' 00:00:00';
                $end = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 day')) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == 'time') {
                $time_type = $get['time_type'];
                if (isset($get['last_date']) && $get['last_date'] !== '') {
                    list($start, $end) = explode('-', str_replace('+', '', $get['last_date']));
                    $db->whereBetween($time_type, ["{$start} 00:00:00", "{$end} 23:59:59"]);
                }
            }


        }
        foreach (['file_id', 'customer_type_id', 'department_id'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, '=', "$get[$key]");
        }

        $db->whereNull('is_rubbish');
        $db->whereNull('is_public');
        $db->whereNull('is_quit');
        $db->where('is_deleted', 0);
        $this->assign('department_id', $department_id);

        return parent::_list($db);
    }

    protected function _index9_data_filter(&$data)
    {
        $get = $this->request->get();

        $user = session('user');
        $city_list = Cache::store('redis')->get('city_list');
        $this->assign('city_list', $city_list);

        // $file_list=Cache::store('redis')->get('file_list');
        $customer_type_list = Cache::store('redis')->get('customer_type_list');
        $this->assign('num_list', count($data));
        $this->assign('department_id', $user['department_id']);
        $file_list = Cache::store('redis')->get('file_list');
        $this->assign('file_list', $file_list);
        $this->assign('customer_type_list', $customer_type_list);

    }

    public function dispatchedToFollowed()
    {
        $ids = $this->request->post('ids/a', []);
        $msg = '转移失败';

        if (empty($ids) === false) {
            $re = Db::name($this->table)
                ->whereIn('id', $ids)
                ->update(['status' => 1, 'create_id' => 100011]);
            if ($re) {
                $msg = '转移成功';
            }
        }

        $this->success($msg);
    }

    /**
     * 权限列表
     */
    public function index()
    {
        $this->title = '未处理客户管理';
        $user = session('user');
        $department_id = $user['department_id'];

        if ($department_id == 1) {
            $db = Db::name($this->table)->whereNotIn('status', [9, 15, 19])->order('create_time desc');
            $db2 = Db::name($this->table)->whereNotIn('status', [9, 15, 19])->order('create_time desc');
        } else {
            $db = Db::name($this->table)->where('status', 0)->order('create_time desc');
            $db2 = Db::name($this->table)->where('status', 0)->order('create_time desc');
        }


        $get = $this->request->get();
        if (isset($get['customer_content']) && $get['customer_content'] !== '') {
            $customer_content = $get['customer_content'];

            $db->where($get['customer_condition'], 'like', "%$customer_content%");
            $db2->where($get['customer_condition'], 'like', "%$customer_content%");
        }
        if (isset($get['condition']) && $get['condition'] !== '') {
            $condition = $get['condition'];

            $db->where($get['loan_conditions'], $condition);
            $db2->where($get['loan_conditions'], $condition);
        }
        if (isset($get['remark_time']) && $get['remark_time'] !== '') {
            $day = $get['remark_time'];
            $start = date("Y-m-d", strtotime("-$day day")) . ' 00:00:00';
            $db->where('remark_time', '<', $start);
            $db2->where('remark_time', '<', $start);
        }
        if (isset($get['time_content']) && $get['time_content'] !== '') {
            $condition = $get['time_content'];

            if ($condition == '1') {
                $start = date("Y/m/d") . ' 00:00:00';
                $db->where($get['time_type'], '>=', $start);
                $db2->where($get['time_type'], '>=', $start);
            } elseif ($condition == '-1') {
                $start = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';
                $end = date("Y-m-d", strtotime("-1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
                $db2->where($get['time_type'], '>=', $start);
                $db2->where($get['time_type'], '<=', $end);
            } elseif ($condition == '7') {
                $start = date("Y-m-d", strtotime("-7 day")) . ' 00:00:00';
                $end = date("Y/m/d") . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
                $db2->where($get['time_type'], '>=', $start);
                $db2->where($get['time_type'], '<=', $end);
            } elseif ($condition == '30') {
                $start = date('Y-m-01', strtotime(date("Y-m-d"))) . ' 00:00:00';
                $end = date('Y-m-d', strtotime("$start +1 month -1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
                $db2->where($get['time_type'], '>=', $start);
                $db2->where($get['time_type'], '<=', $end);
            } elseif ($condition == '-30') {
                $start = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month')) . ' 00:00:00';
                $end = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 day')) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
                $db2->where($get['time_type'], '>=', $start);
                $db2->where($get['time_type'], '<=', $end);
            } elseif ($condition == 'time') {
                $time_type = $get['time_type'];
                if (isset($get['last_date']) && $get['last_date'] !== '') {
                    list($start, $end) = explode('-', str_replace('+', '', $get['last_date']));
                    $db->whereBetween($time_type, ["{$start} 00:00:00", "{$end} 23:59:59"]);
                    $db2->whereBetween($time_type, ["{$start} 00:00:00", "{$end} 23:59:59"]);
                }
            }


        }

        if (isset($get['loan_limit']) && $get['loan_limit'] !== '') {
            $condition = $get['loan_limit'];

            if ($condition == '1') {


                $db->where('money_demand', '>=', 10000);
                $db->where('money_demand', '<=', 50000);
                $db2->where('money_demand', '>=', 10000);
                $db2->where('money_demand', '<=', 50000);
            } elseif ($condition == '2') {

                $db->where('money_demand', '>=', 50000);
                $db->where('money_demand', '<=', 100000);
                $db2->where('money_demand', '>=', 50000);
                $db2->where('money_demand', '<=', 100000);
            } elseif ($condition == '3') {
                $db->where('money_demand', '>=', 100000);
                $db->where('money_demand', '<=', 200000);
                $db2->where('money_demand', '>=', 100000);
                $db2->where('money_demand', '<=', 200000);
            } elseif ($condition == '4') {
                $minimum = $get['minimum'];
                $highest = $get['highest'];
                $db->where('money_demand', '>=', $minimum);
                $db->where('money_demand', '<=', $highest);
                $db2->where('money_demand', '>=', $minimum);
                $db2->where('money_demand', '<=', $highest);
            }


        }
        foreach (['department_id', 'file_id', 'user_id', 'customer_type_id', 'status', 'single_piece_type', 'is_repeat'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, '=', "$get[$key]");
            (isset($get[$key]) && $get[$key] !== '') && $db2->where($key, '=', "$get[$key]");
        }

        $db->whereNull('is_rubbish');
        $db->whereNull('is_public');
        $db->whereNull('is_quit');
        $db->where('is_deleted', 0);
        $db2->whereNull('is_rubbish');
        $db2->whereNull('is_public');
        $db2->whereNull('is_quit');
        if ($user['department_id'] != 1) {
            $db->where('user_id', $user['id']);
            $db2->where('user_id', $user['id']);
        }

        $this->assign('department_id', $department_id);
        session('xygsql', $db2->buildSql());
        return parent::_list($db);
    }

    public function getIndex1Count()
    {

        $user = session('user');

        $operation_user = Cache::store('redis')->get('department_tree' . $user['department_id']);
        $id_list = '';
        foreach ($operation_user as $city) {
            $id_list = $id_list . $city . ',';
        }
        $id_list = substr($id_list, 0, strlen($id_list) - 1);


        $dgj1 = Db::query("SELECT count(*)as num from crm_customer where `status`=1  AND `is_deleted` = 0 and user_id is not null and department_id in ($id_list) and DATE_SUB(CURDATE(), INTERVAL 1 DAY) >= date(last_time)  AND is_quit IS NULL");
        //$this->assign('dgjsl', $dgjsl[0]['num']);
        $dqy9_2_3 = Db::query("SELECT count(*)as num from crm_customer where `status`=3  AND `is_deleted` = 0 and user_id is not null and department_id in ($id_list) and DATE_SUB(CURDATE(), INTERVAL 9 DAY) >= date(last_time)  AND is_quit IS NULL and customer_type_id>=2 and customer_type_id<=3 ");
        //$this->assign('srdgjsl', $dgjs2[0]['num']);
        $dgj10 = Db::query("SELECT count(*)as num from crm_customer where `status`=3  AND `is_deleted` = 0 and user_id is not null and department_id in ($id_list) and DATE_SUB(CURDATE(), INTERVAL 10 DAY) >= date(last_time)  AND is_quit IS NULL ");
        $dgj10_3 = Db::query("SELECT count(*)as num from crm_customer where `status`=3  AND `is_deleted` = 0 and user_id is not null and department_id in ($id_list) and DATE_SUB(CURDATE(), INTERVAL 10 DAY) >= date(last_time)  AND is_quit IS NULL and customer_type_id>=3 ");

        $dgj29 = Db::query("SELECT count(*)as num from crm_customer where `status`=3  AND `is_deleted` = 0 and user_id is not null and department_id in ($id_list) and DATE_SUB(CURDATE(), INTERVAL 29 DAY) >= date(last_time)  AND is_quit IS NULL  ");
        $dgj29_3 = Db::query("SELECT count(*)as num from crm_customer where `status`=3  AND `is_deleted` = 0 and user_id is not null and department_id in ($id_list) and DATE_SUB(CURDATE(), INTERVAL 29 DAY) >= date(last_time)  AND is_quit IS NULL and customer_type_id>=3 ");

        $dgj4_4 = Db::query("SELECT count(*)as num from crm_customer where `status`=3  AND `is_deleted` = 0 and user_id is not null and department_id in ($id_list) and DATE_SUB(CURDATE(), INTERVAL 4 DAY) >= date(last_time)  AND is_quit IS NULL and customer_type_id>=4 ");
        return json(['data' => ['dgj1' => $dgj1[0]['num'], 'dqy9_2_3' => $dqy9_2_3[0]['num'],
            'dgj10' => $dgj10[0]['num'], 'dgj10_3' => $dgj10_3[0]['num'],
            'dgj29' => $dgj29[0]['num'], 'dgj29_3' => $dgj29_3[0]['num'], 'dgj4_4' => $dgj4_4[0]['num']]], 200);
    }

    public function index1()
    {
        $this->title = '团队客户';
        $user = session('user');
        $user_id = $user['id'];
        $sort = Cache::store('redis')->get('index1' . "_$user_id");
        $db = Db::name($this->table);

        if ($sort == false) {
            $db->order('create_time desc')->order('field(`status`,0) desc ')->order(' field(`customer_type_id`,\'5\',\'4\',\'3\',\'2\',\'1\',\'6\') ');
        } else {
            $order = explode(',', $sort['order']);
//            $db->order('create_time desc');
            foreach ($order as $vo) {
                if ($vo == 'customer_type_id') {
                    $customer_type_id_sort = $sort['customer_type_id'];
                    $customer_type_id = "field(`customer_type_id`,$customer_type_id_sort)" . ' desc';
                    $db->order("$customer_type_id");

                } elseif ($vo == 'status') {
                    $status_sort = $sort['status'];
                    $status = "field(`status`,$status_sort)" . ' desc';
                    $db->order("$status");
                } elseif ($vo == 'new_data') {
                    $new_data = "field(`new_data`,1)" . ' desc';
                    $db->order("$new_data");
                } elseif ($vo == 'is_read') {
                    $db->order('is_read,comment desc');
                } else {
                    $db->order($vo . ' ' . 'desc');
                }
            }
        }
        if ($user['authorize'] == 1 || $user['authorize'] == 11) {
            $this->assign('isdel', '1');
        } else {
            $this->assign('isdel', '0');
        }
        $get = $this->request->get();

        $db->whereNull('is_rubbish');
        $db->whereNull('is_public');
        $db->whereNull('is_quit');
        $db->whereNotNull('user_id');
        $db->where('is_deleted', 0);
        //不查询垃圾库的数据
        // $db->whereOr('user_id','<>',10012);
        //$db->whereOr('user_id','null');

        if (isset($get['is_history']) && $get['is_history'] != '') {

            $file_list = Db::name("CrmFile")->where('type', 2)->field('id')->select();
            $t = '';
            foreach ($file_list as $v) {
                $v = join(",", $v); // 可以用implode将一维数组转换为用逗号连接的字符串，join是别名
                $temp[] = $v;
            }
            $file_list = implode(',', $temp);
            if ($get['is_history'] == 1) {
                $db->whereIn('file_id', $file_list);
            } else {
                $db->whereNotIn('file_id', $file_list);
            }

        }
        if (isset($get['remark_time']) && $get['remark_time'] !== '') {
            $day = $get['remark_time'];
            $start = date("Y-m-d", strtotime("-$day day")) . ' 00:00:00';
            $db->where('remark_time', '<', $start);

        }
        if (isset($get['remark_times_condition']) && $get['remark_times_condition'] !== '' && isset($get['remark_times']) && $get['remark_times'] !== '') {
            $remark_times = $get['remark_times'];
            $remark_times_condition = html_entity_decode(urldecode($get['remark_times_condition']));
            $db->where('remark_times', $remark_times_condition, $remark_times);
        }
        if (isset($get['is_reassign']) && $get['is_reassign'] !== '') {

            if ($get['is_reassign'] == 3) {
                $db->where('is_reassign', 0);
            } else {
                $db->where('is_reassign', $get['is_reassign']);
            }

            $this->assign('is_reassign', $get['is_reassign']);

        }
        if (isset($get['is_follow']) && $get['is_follow'] != '') {


            if ($get['is_follow'] == 1) {
                $db->whereNull('remark_time');

            } else {
                $db->whereNotNull('remark_time');
            }

        }


        if (isset($get['department_id1']) && $get['department_id1'] != '') {
            $department_id = $get['department_id1'];

            if (isset($get['department_id2']) && $get['department_id2'] != '') {
                $department_id = $get['department_id2'];

                if (isset($get['department_id3']) && $get['department_id3'] != '') {
                    $department_id = $get['department_id3'];

                }
            }
            $department_list = Cache::store('redis')->get('department_tree' . $department_id);
            $db->where('department_id', 'in', $department_list);
        }
        if (isset($get['customer_content']) && $get['customer_content'] !== '') {
            $customer_content = $get['customer_content'];

            $db->where($get['customer_condition'], 'like', "%$customer_content%");

        }
        if (isset($get['condition']) && $get['condition'] !== '') {
            $condition = $get['condition'];

            $db->where($get['loan_conditions'], $condition);

        }
        if (isset($get['remark_time']) && $get['remark_time'] !== '') {
            $day = $get['remark_time'];
            $start = date("Y-m-d", strtotime("-$day day")) . ' 00:00:00';
            $db->where('remark_time', '<', $start);

        }
        if (isset($get['time_content']) && $get['time_content'] !== '') {
            $condition = $get['time_content'];

            if ($condition == '1') {
                $start = date("Y/m/d") . ' 00:00:00';
                $db->where($get['time_type'], '>=', $start);
            } elseif ($condition == '-1') {
                $start = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';
                $end = date("Y-m-d", strtotime("-1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '7') {
                $start = date("Y-m-d", strtotime("-7 day")) . ' 00:00:00';
                $end = date("Y/m/d") . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '30') {
                $start = date('Y-m-01', strtotime(date("Y-m-d"))) . ' 00:00:00';
                $end = date('Y-m-d', strtotime("$start +1 month -1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '-30') {
                $start = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month')) . ' 00:00:00';
                $end = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 day')) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == 'time') {
                $time_type = $get['time_type'];
                if (isset($get['last_date']) && $get['last_date'] !== '') {
                    list($start, $end) = explode('-', str_replace('+', '', $get['last_date']));
                    $db->whereBetween($time_type, ["{$start} 00:00:00", "{$end} 23:59:59"]);
                }
            }


        }
        if (isset($get['last_time']) && $get['last_time'] !== '') {
            $condition = $get['last_time'];
            /*if($condition == '-1'){
                $end=date("Y-m-d",strtotime("-1 day")).' 23:59:59';
                $db->where($get['last_time'], '<=',$end);
            }elseif*/
            switch ($condition) {
                case "-1":
                    $end = date("Y-m-d", strtotime("-1 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-2":
                    $end = date("Y-m-d", strtotime("-2 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-3":
                    $end = date("Y-m-d", strtotime("-3 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-4":
                    $end = date("Y-m-d", strtotime("-4 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-5":
                    $end = date("Y-m-d", strtotime("-5 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-6":
                    $end = date("Y-m-d", strtotime("-6 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-7":
                    $end = date("Y-m-d", strtotime("-7 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-8":
                    $end = date("Y-m-d", strtotime("-8 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-9":
                    $end = date("Y-m-d", strtotime("-9 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-10":
                    $end = date("Y-m-d", strtotime("-10 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-11":
                    $end = date("Y-m-d", strtotime("-11 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-12":
                    $end = date("Y-m-d", strtotime("-12 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-13":
                    $end = date("Y-m-d", strtotime("-13 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-14":
                    $end = date("Y-m-d", strtotime("-14 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-15":
                    $end = date("Y-m-d", strtotime("-15 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
            }
        }
        if (isset($get['loan_limit']) && $get['loan_limit'] !== '') {
            $condition = $get['loan_limit'];

            if ($condition == '1') {


                $db->where('money_demand', '>=', 10000);
                $db->where('money_demand', '<=', 50000);
            } elseif ($condition == '2') {

                $db->where('money_demand', '>=', 50000);
                $db->where('money_demand', '<=', 100000);
            } elseif ($condition == '3') {
                $db->where('money_demand', '>=', 100000);
                $db->where('money_demand', '<=', 200000);
            } elseif ($condition == '4') {
                $minimum = $get['minimum'];
                $highest = $get['highest'];
                $db->where('money_demand', '>=', $minimum);
                $db->where('money_demand', '<=', $highest);
            }


        }
        foreach (['department_id', 'file_id', 'user_id', 'customer_type_id', 'status', 'single_piece_type', 'sex', 'new_data'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, '=', "$get[$key]");
        }
        foreach (['city'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $value = trim($get[$key]);
                $db->where($key, 'like', "%$value%");
            }
        }
        foreach (['remarks'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%$get[$key]%");
            }
        }
        $department_id = $user['department_id'];

        if ($department_id == 1) {
            if (count($get) == 1) {
//                $db->where('status',0);
                $this->assign('status', 0);
            }
            $this->assign('isboss', '1');
        } else {
            $authorize = $user['authorize'];
            $i = Db::name("Department")->where('pid', $department_id)->count();
            //$list = Db::query("SELECT * from department where pid=$department_id");
            if ($authorize == 6 || $authorize == 8 || $authorize = 11) {
                $this->assign('isboss', '1');
                $department_list = Cache::store('redis')->get('department_tree' . $user['department_id']);
                $db->where('department_id', 'in', $department_list);
            } else {

                $this->assign('isboss', '0');
                $db->where('department_id', $user['department_id']);
            }
        }
        $this->assign('department_id', $department_id);
        if (isset($get['department_id']) && $get['department_id'] !== "") {
            $user_list = Cache::store('redis')->get('department_user' . $get['department_id']);
        } else {
            $user_list = Cache::store('redis')->get('operation_user' . $user['id']);
        }
	//Log::record('-----------'.$department_id.'--------'.json_encode($user_list));
        $this->assign('user_list', $user_list);
        $operation_user = Cache::store('redis')->get('department_tree' . $user['department_id']);
        $id_list = '';
        foreach ($operation_user as $city) {
            $id_list = $id_list . $city . ',';
        }
        $id_list = substr($id_list, 0, strlen($id_list) - 1);


        if ($department_id == 1) {
            //$dgjsl = Db::query("SELECT count(*)as num from crm_customer where `status`=1 and user_id is not null and DATE_SUB(CURDATE(), INTERVAL 1 DAY) >= date(last_time)  and  is_quit IS NULL");
            $this->assign('dgjsl', 0);
            // $wgjsj = Db::query("SELECT count(*)as num from crm_customer where   user_id is not null  and DATE_SUB(CURDATE(), INTERVAL 9 DAY) >= date(last_time) AND is_quit IS NULL");
            $this->assign('wgjsj', 0);
            //$exwgjsj = Db::query("SELECT count(*)as num from crm_customer where     user_id is not null  and DATE_SUB(CURDATE(), INTERVAL 9 DAY) >= date(last_time) and customer_type_id>=2 and customer_type_id<=5  AND is_quit IS NULL");
            $this->assign('exwgjsj', 0);
            //$ebwgjsj = Db::query("SELECT count(*)as num from crm_customer where     user_id is not null  and DATE_SUB(CURDATE(), INTERVAL 28 DAY) >= date(last_time)  AND is_quit IS NULL");
            $this->assign('ebwgjsj', 0);
            // $exebwgjsj = Db::query("SELECT count(*)as num from crm_customer where   user_id is not null and DATE_SUB(CURDATE(), INTERVAL 28 DAY) >= date(last_time) and customer_type_id>=2 and customer_type_id<=5 AND is_quit IS NULL");
            $this->assign('exebwgjsj', 0);
        } else {
            /*
                        $dgjsl = Db::query("SELECT count(*)as num from crm_customer where `status`=1  AND `is_deleted` = 0 and user_id is not null and department_id in ($id_list) and DATE_SUB(CURDATE(), INTERVAL 1 DAY) >= date(last_time)  AND is_quit IS NULL");
                        $this->assign('dgjsl', $dgjsl[0]['num']);
                        $dgjsl = Db::query("SELECT count(*)as num from crm_customer where `status`=1  AND `is_deleted` = 0 and user_id is not null and department_id in ($id_list) and DATE_SUB(CURDATE(), INTERVAL 3 DAY) >= date(last_time)  AND is_quit IS NULL");
                        $this->assign('srdgjsl', $dgjsl[0]['num']);
                        $wgjsj = Db::query("SELECT count(*)as num from crm_customer where user_id is not null AND `is_deleted` = 0 and  department_id in ($id_list) and DATE_SUB(CURDATE(), INTERVAL 10 DAY) >= date(last_time) AND is_quit IS NULL");
                        $this->assign('wgjsj', $wgjsj[0]['num']);
                        $exwgjsj = Db::query("SELECT count(*)as num from crm_customer where    user_id is not null AND `is_deleted` = 0 and  department_id in ($id_list) and DATE_SUB(CURDATE(), INTERVAL 10 DAY) >= date(last_time) and customer_type_id>2 and customer_type_id<=5   AND is_quit IS NULL");
                        $this->assign('exwgjsj', $exwgjsj[0]['num']);
                        $ebwgjsj = Db::query("SELECT count(*)as num from crm_customer where    user_id is not null  AND `is_deleted` = 0 and  department_id in ($id_list) and DATE_SUB(CURDATE(), INTERVAL 30 DAY) >= date(last_time)  AND is_quit IS NULL");
                        $this->assign('ebwgjsj', $ebwgjsj[0]['num']);
                        $exebwgjsj = Db::query("SELECT count(*)as num from crm_customer where user_id is not null  AND `is_deleted` = 0 and   department_id in ($id_list) and DATE_SUB(CURDATE(), INTERVAL 30 DAY) >= date(last_time) and customer_type_id>2 and customer_type_id<=5   AND is_quit IS NULL");
                        $this->assign('exebwgjsj', $exebwgjsj[0]['num']);
                */
        }

        if (isset($get['sjzt']) && $get['sjzt'] !== "") {
            $day = $get['sjzt'];
            $sj = date("Y-m-d 23:59:59", strtotime("-$day day"));
            $db->where('last_time', '<=', $sj);

        }
        if (isset($get['custype']) && $get['custype'] !== "") {

            $db->where('customer_type_id', '>=', 2)->where('customer_type_id', '<=', 5);

        }
        $this->assign('user_id', $user_id);
//        print_r($db);exit;
        return parent::_list($db);
    }

    public function index2()
    {
        $this->title = '我的客户';
        $user = session('user');
        $user_id = $user['id'];
        $get = $this->request->get();
        $db = Db::name($this->table)->where('status', '<>', 0);
        $db2 = Db::name($this->table)->where('status', '<>', 0);
        $db->where('is_reassign', 0);
//            $where = array('is_reassign'=>[['eq','null'],['eq',0],'or']);
//            $db->where($where);
        $db2->where('is_reassign', 0);
//            $db2->where($where);
        $this->assign('my', 'my');
        $this->assign('is_reassign', 'is_reassign');
//         print_r($get);exit;
//        if(isset($get['is_reassign'])&& $get['is_reassign'] == 'is_reassign'&&isset($get['my'])&& $get['my'] == 'my'){
//            $db = Db::name($this->table)->where('status','<>',0);
//            $db2 = Db::name($this->table)->where('status','<>',0);
//            $db->where('is_reassign',0);
////            $where = array('is_reassign'=>[['eq','null'],['eq',0],'or']);
////            $db->where($where);
//            $db2->where('is_reassign',0);
////            $db2->where($where);
//            $this->assign('my', 'my');
//            $this->assign('is_reassign', 'is_reassign');
//        }else if(isset($get['is_reassign'])&& $get['is_reassign'] == 'is_reassign'){
//            $db = Db::name($this->table)->where('status','<>',0)->where('is_reassign','null');
//            $db2 = Db::name($this->table)->where('status','<>',0)->where('is_reassign','null');
//            $this->assign('my', '0');
//            $this->assign('is_reassign', 'is_reassign');
//        }else if(isset($get['my'])&& $get['my'] == 'my'){
//            $db = Db::name($this->table)->where('status','<>',0)->where('is_reassign','null');
//            $db2 = Db::name($this->table)->where('status','<>',0)->where('is_reassign','null');
//            $this->assign('is_reassign', '0');
//            $this->assign('my', 'my');
//        }else{
//            $db = Db::name($this->table)->where('status','<>',0)->where('is_reassign','null');
//            $db2 = Db::name($this->table)->where('status','<>',0)->where('is_reassign','null');
//            $this->assign('my', 'my');
//            $this->assign('is_reassign', 'is_reassign');
//        }
        $sort = Cache::store('redis')->get('index2' . "_$user_id");
        if (isset($get['last_time']) && $get['last_time'] !== '') {
            $condition = $get['last_time'];
            /*if($condition == '-1'){
                $end=date("Y-m-d",strtotime("-1 day")).' 23:59:59';
                $db->where($get['last_time'], '<=',$end);
            }elseif*/
            switch ($condition) {
                case "-1":
                    $end = date("Y-m-d", strtotime("-1 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-2":
                    $end = date("Y-m-d", strtotime("-2 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-3":
                    $end = date("Y-m-d", strtotime("-3 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-4":
                    $end = date("Y-m-d", strtotime("-4 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-5":
                    $end = date("Y-m-d", strtotime("-5 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-6":
                    $end = date("Y-m-d", strtotime("-6 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-7":
                    $end = date("Y-m-d", strtotime("-7 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-8":
                    $end = date("Y-m-d", strtotime("-8 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-9":
                    $end = date("Y-m-d", strtotime("-9 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-10":
                    $end = date("Y-m-d", strtotime("-10 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-11":
                    $end = date("Y-m-d", strtotime("-11 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-12":
                    $end = date("Y-m-d", strtotime("-12 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-13":
                    $end = date("Y-m-d", strtotime("-13 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-14":
                    $end = date("Y-m-d", strtotime("-14 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-15":
                    $end = date("Y-m-d", strtotime("-15 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
            }
        }
        // $db->order('is_read,comment desc')->order('new_data');
        // $db2->order('is_read,comment desc')->order('new_data');
        // $db = Db::name($this->table)->where('status','<>',0)->where('status','<>',6)->where('is_reassign','null')->order('field(`status`,0) desc ')->order(' field(`customer_type_id`,\'5\',\'4\',\'3\',\'2\',\'1\',\'6\') ')->order('create_time desc');
        if ($sort == false) {
            //$db->order('is_read,comment desc')->order('new_data');
            //$db2->order('is_read,comment desc')->order('new_data');
        } else {
            $order = explode(',', $sort['order']);

            foreach ($order as $vo) {
                if ($vo == 'customer_type_id') {
                    $customer_type_id_sort = $sort['customer_type_id'];
                    $customer_type_id = "field(`customer_type_id`,$customer_type_id_sort)" . ' desc';
                    $db->order("$customer_type_id");
                    $db2->order("$customer_type_id");
                } elseif ($vo == 'status') {
                    $status_sort = $sort['status'];
                    $status = "field(`status`,$status_sort)" . ' desc';
                    $db->order("$status");
                    $db2->order("$status");
                } elseif ($vo == 'new_data') {
                    $new_data = "field(`new_data`,1)" . ' desc';
                    $db->order("$new_data");
                    $db2->order("$new_data");
                } elseif ($vo == 'is_read') {
                    $db->order('is_read,comment desc');
                    $db2->order('is_read,comment desc');
                } else {
                    $db->order($vo . ' ' . 'desc');
                    $db2->order($vo . ' ' . 'desc');
                }

            }


        }

        if (isset($get['customer_content']) && $get['customer_content'] !== '') {
            $customer_content = $get['customer_content'];

            $db->where($get['customer_condition'], 'like', "%$customer_content%");
            $db2->where($get['customer_condition'], 'like', "%$customer_content%");
        }
        if (isset($get['condition']) && $get['condition'] !== '') {
            $condition = $get['condition'];

            $db->where($get['loan_conditions'], $condition);
            $db2->where($get['loan_conditions'], $condition);
        }
        if (isset($get['remark_time']) && $get['remark_time'] !== '') {
            $day = $get['remark_time'];
            $start = date("Y-m-d", strtotime("-$day day")) . ' 00:00:00';
            $db->where('remark_time', '<', $start);
            $db2->where('remark_time', '<', $start);
        }
        if (isset($get['remark_times_condition']) && $get['remark_times_condition'] !== '' && isset($get['remark_times']) && $get['remark_times'] !== '') {
            $remark_times = $get['remark_times'];
            $remark_times_condition = html_entity_decode(urldecode($get['remark_times_condition']));
            $db->where('remark_times', $remark_times_condition, $remark_times);
            $db2->where('remark_times', $remark_times_condition, $remark_times);
        }
        if (isset($get['time_content']) && $get['time_content'] !== '') {
            $condition = $get['time_content'];

            if ($condition == '1') {
                $start = date("Y/m/d") . ' 00:00:00';
                $db->where($get['time_type'], '>=', $start);
                $db2->where($get['time_type'], '>=', $start);
            } elseif ($condition == '-1') {
                $start = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';
                $end = date("Y-m-d", strtotime("-1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
                $db2->where($get['time_type'], '>=', $start);
                $db2->where($get['time_type'], '<=', $end);
            } elseif ($condition == '7') {
                $start = date("Y-m-d", strtotime("-7 day")) . ' 00:00:00';
                $end = date("Y/m/d") . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
                $db2->where($get['time_type'], '>=', $start);
                $db2->where($get['time_type'], '<=', $end);
            } elseif ($condition == '30') {
                $start = date('Y-m-01', strtotime(date("Y-m-d"))) . ' 00:00:00';
                $end = date('Y-m-d', strtotime("$start +1 month -1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
                $db2->where($get['time_type'], '>=', $start);
                $db2->where($get['time_type'], '<=', $end);
            } elseif ($condition == '-30') {
                $start = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month')) . ' 00:00:00';
                $end = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 day')) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
                $db2->where($get['time_type'], '>=', $start);
                $db2->where($get['time_type'], '<=', $end);
            } elseif ($condition == 'time') {
                $time_type = $get['time_type'];
                if (isset($get['last_date']) && $get['last_date'] !== '') {
                    list($start, $end) = explode('-', str_replace('+', '', $get['last_date']));
                    $db->whereBetween($time_type, ["{$start} 00:00:00", "{$end} 23:59:59"]);
                    $db2->whereBetween($time_type, ["{$start} 00:00:00", "{$end} 23:59:59"]);
                }
            }


        }
        if (isset($get['remark_time']) && $get['remark_time'] !== '') {
            $day = $get['remark_time'];
            $start = date("Y-m-d", strtotime("-$day day")) . ' 00:00:00';
            $db->where('remark_time', '<', $start);

        }
        if (isset($get['loan_limit']) && $get['loan_limit'] !== '') {
            $condition = $get['loan_limit'];

            if ($condition == '1') {


                $db->where('money_demand', '>=', 10000);
                $db->where('money_demand', '<=', 50000);
                $db2->where('money_demand', '>=', 10000);
                $db2->where('money_demand', '<=', 50000);
            } elseif ($condition == '2') {

                $db->where('money_demand', '>=', 50000);
                $db->where('money_demand', '<=', 100000);
                $db2->where('money_demand', '>=', 50000);
                $db2->where('money_demand', '<=', 100000);
            } elseif ($condition == '3') {
                $db->where('money_demand', '>=', 100000);
                $db->where('money_demand', '<=', 200000);
                $db2->where('money_demand', '>=', 100000);
                $db2->where('money_demand', '<=', 200000);
            } elseif ($condition == '4') {
                $minimum = $get['minimum'];
                $highest = $get['highest'];
                $db->where('money_demand', '>=', $minimum);
                $db->where('money_demand', '<=', $highest);
                $db2->where('money_demand', '>=', $minimum);
                $db2->where('money_demand', '<=', $highest);
            }


        }
        foreach (['department_id', 'file_id', 'user_id', 'customer_type_id', 'status', 'single_piece_type', 'sex', 'new_data'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, '=', "$get[$key]");
            (isset($get[$key]) && $get[$key] !== '') && $db2->where($key, '=', "$get[$key]");
        }
        foreach (['remarks'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%$get[$key]%");
            }
        }
        // if($user['department_id']!=1){
        $db->where('user_id', $user['id']);
        $db2->where('user_id', $user['id']);
        // }
        if (isset($get['is_history']) && $get['is_history'] != '') {

            $file_list = Db::name("CrmFile")->where('type', 2)->field('id')->select();
            $t = '';
            $temp = [];
            foreach ($file_list as $v) {
                $v = join(",", $v); // 可以用implode将一维数组转换为用逗号连接的字符串，join是别名
                $temp[] = $v;
            }
            $file_list = implode(',', $temp);
            if ($get['is_history'] == 1) {
                $db->whereIn('file_id', $file_list);
                $db2->whereIn('file_id', $file_list);
            } else {
                $db->whereNotIn('file_id', $file_list);
                $db2->whereNotIn('file_id', $file_list);
            }

        }
        if (isset($get['is_follow']) && $get['is_follow'] != '') {


            if ($get['is_follow'] == 1) {
                $db->whereNull('remark_time');

            } else {
                $db->whereNotNull('remark_time');
            }

        }
        $db->whereNull('is_rubbish');
        $db->whereNull('is_public');
        $db->whereNull('is_quit');
        $db->where('is_deleted', 0);
        $db2->whereNull('is_rubbish');
        $db2->whereNull('is_public');
        $db2->whereNull('is_quit');
        $db2->where('is_deleted', 0);
        $department_id = $user['department_id'];
        $this->assign('department_id', $department_id);

        session('xygsql', $db2->buildSql());


        //“待跟进”客户超过1天未跟进
        $dgj1 = Db::query("SELECT count(*)as num from crm_customer where `status`=1 and is_reassign=0   AND `is_deleted` = 0 and user_id='$user_id' and CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY),' 23:59:59') >= date(last_time)");
        $this->assign('dgj1', $dgj1[0]['num']);
        //2-3星“待签约”客户超过9天未跟进
        $dqy9_2_3 = Db::query("SELECT count(*)as num from crm_customer where `status`=3 and customer_type_id between 2 and 3 and is_reassign=0  AND `is_deleted` = 0 and user_id='$user_id' and CONCAT(DATE_SUB(CURDATE(), INTERVAL 9 DAY),' 23:59:59') >= date(last_time)");
        $this->assign('dqy9_2_3', $dqy9_2_3[0]['num']);

        //超过10天未跟进
        $dgj10 = Db::query("SELECT count(*)as num from crm_customer where  `status` <> 0 and user_id='$user_id' and is_reassign=0 AND `is_deleted` = 0  and CONCAT(DATE_SUB(CURDATE(), INTERVAL 10 DAY),' 23:59:59') >= date(last_time)");
        $this->assign('dgj10', $dgj10[0]['num']);

        //超过10天未跟进的三星以上
        $dgj10_3 = Db::query("SELECT count(*)as num from crm_customer where  `status` <> 0 and   user_id='$user_id' and is_reassign=0 and customer_type_id >=3 AND `is_deleted` = 0  and CONCAT(DATE_SUB(CURDATE(), INTERVAL 10 DAY),' 23:59:59') >= date(last_time)");
        $this->assign('dgj10_3', $dgj10_3[0]['num']);

        //超过29天未跟进
        $dgj29 = Db::query("SELECT count(*)as num from crm_customer where  `status` <> 0 and   user_id='$user_id' and is_reassign=0 AND `is_deleted` = 0  and CONCAT(DATE_SUB(CURDATE(), INTERVAL 29 DAY),' 23:59:59') >= date(last_time)");
        $this->assign('dgj29', $dgj29[0]['num']);

        //超过29天未跟进的三星以上
        $dgj29_3 = Db::query("SELECT count(*)as num from crm_customer where  `status` <> 0 and   user_id='$user_id' and is_reassign=0 and customer_type_id >=3 AND `is_deleted` = 0  and CONCAT(DATE_SUB(CURDATE(), INTERVAL 29 DAY),' 23:59:59') >= date(last_time)");
        $this->assign('dgj29_3', $dgj29_3[0]['num']);

        //4星以上超过4天未跟进
        $dgj4_4 = Db::query("SELECT count(*)as num from crm_customer where  `status` <> 0 and   user_id='$user_id' and is_reassign=0 and customer_type_id >=4 AND `is_deleted` = 0  and CONCAT(DATE_SUB(CURDATE(), INTERVAL 4 DAY),' 23:59:59') >= date(last_time)");
        $this->assign('dgj4_4', $dgj4_4[0]['num']);

        if (isset($get['sjzt']) && $get['sjzt'] !== "") {
            $day = $get['sjzt'];


            $sj = date("Y-m-d 23:59:59", strtotime("-$day day"));
            $db->where('last_time', '<=', $sj);

        }


        if (isset($get['custype_l']) && $get['custype_l'] !== "") {

            $db->where('customer_type_id', '>=', $get['custype_l']);

        }

        if (isset($get['custype_r']) && $get['custype_r'] !== "") {

            $db->where('customer_type_id', '<=', $get['custype_r']);

        }

        if (isset($get['custype']) && $get['custype'] !== "") {

            $db->where('customer_type_id', '>=', 2)->where('customer_type_id', '<=', 5);

        }


        return parent::_list($db);

    }

    public function index10()
    {
        $this->title = '重要客户';
        $user = session('user');
        $user_id = $user['id'];
        $get = $this->request->get();
//         print_r($get);exit;
        $db = Db::name($this->table)->where('is_reassign', 2);
        $db2 = Db::name($this->table)->where('is_reassign', 2);
        $sort = Cache::store('redis')->get('index2' . "_$user_id");
        if (isset($get['last_time']) && $get['last_time'] !== '') {
            $condition = $get['last_time'];
            /*if($condition == '-1'){
                $end=date("Y-m-d",strtotime("-1 day")).' 23:59:59';
                $db->where($get['last_time'], '<=',$end);
            }elseif*/
            switch ($condition) {
                case "-1":
                    $end = date("Y-m-d", strtotime("-1 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-2":
                    $end = date("Y-m-d", strtotime("-2 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-3":
                    $end = date("Y-m-d", strtotime("-3 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-4":
                    $end = date("Y-m-d", strtotime("-4 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-5":
                    $end = date("Y-m-d", strtotime("-5 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-6":
                    $end = date("Y-m-d", strtotime("-6 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-7":
                    $end = date("Y-m-d", strtotime("-7 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-8":
                    $end = date("Y-m-d", strtotime("-8 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-9":
                    $end = date("Y-m-d", strtotime("-9 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-10":
                    $end = date("Y-m-d", strtotime("-10 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-11":
                    $end = date("Y-m-d", strtotime("-11 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-12":
                    $end = date("Y-m-d", strtotime("-12 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-13":
                    $end = date("Y-m-d", strtotime("-13 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-14":
                    $end = date("Y-m-d", strtotime("-14 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-15":
                    $end = date("Y-m-d", strtotime("-15 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
            }
        }

        //$db = Db::name($this->table)->where('status','<>',0)->where('status','<>',6)->where('is_reassign','null')->order('field(`status`,0) desc ')->order(' field(`customer_type_id`,\'5\',\'4\',\'3\',\'2\',\'1\',\'6\') ')->order('create_time desc');
        if ($sort == false) {
            // $db->order('is_read,comment desc')->order('new_data');
            // $db2->order('is_read,comment desc')->order('new_data');
        } else {
            $order = explode(',', $sort['order']);

            foreach ($order as $vo) {
                if ($vo == 'customer_type_id') {
                    $customer_type_id_sort = $sort['customer_type_id'];
                    $customer_type_id = "field(`customer_type_id`,$customer_type_id_sort)" . ' desc';
                    $db->order("$customer_type_id");
                    $db2->order("$customer_type_id");
                } elseif ($vo == 'status') {
                    $status_sort = $sort['status'];
                    $status = "field(`status`,$status_sort)" . ' desc';
                    $db->order("$status");
                    $db2->order("$status");
                } elseif ($vo == 'new_data') {
                    $new_data = "field(`new_data`,1)" . ' desc';
                    $db->order("$new_data");
                    $db2->order("$new_data");
                } elseif ($vo == 'is_read') {
                    $db->order('is_read,comment desc');
                    $db2->order('is_read,comment desc');
                } else {
                    $db->order($vo . ' ' . 'desc');
                    $db2->order($vo . ' ' . 'desc');
                }

            }


        }

        if (isset($get['customer_content']) && $get['customer_content'] !== '') {
            $customer_content = $get['customer_content'];

            $db->where($get['customer_condition'], 'like', "%$customer_content%");
            $db2->where($get['customer_condition'], 'like', "%$customer_content%");
        }
        if (isset($get['condition']) && $get['condition'] !== '') {
            $condition = $get['condition'];

            $db->where($get['loan_conditions'], $condition);
            $db2->where($get['loan_conditions'], $condition);
        }
        if (isset($get['remark_time']) && $get['remark_time'] !== '') {
            $day = $get['remark_time'];
            $start = date("Y-m-d", strtotime("-$day day")) . ' 00:00:00';
            $db->where('remark_time', '<', $start);
            $db2->where('remark_time', '<', $start);
        }
        if (isset($get['time_content']) && $get['time_content'] !== '') {
            $condition = $get['time_content'];

            if ($condition == '1') {
                $start = date("Y/m/d") . ' 00:00:00';
                $db->where($get['time_type'], '>=', $start);
                $db2->where($get['time_type'], '>=', $start);
            } elseif ($condition == '-1') {
                $start = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';
                $end = date("Y-m-d", strtotime("-1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
                $db2->where($get['time_type'], '>=', $start);
                $db2->where($get['time_type'], '<=', $end);
            } elseif ($condition == '7') {
                $start = date("Y-m-d", strtotime("-7 day")) . ' 00:00:00';
                $end = date("Y/m/d") . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
                $db2->where($get['time_type'], '>=', $start);
                $db2->where($get['time_type'], '<=', $end);
            } elseif ($condition == '30') {
                $start = date('Y-m-01', strtotime(date("Y-m-d"))) . ' 00:00:00';
                $end = date('Y-m-d', strtotime("$start +1 month -1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
                $db2->where($get['time_type'], '>=', $start);
                $db2->where($get['time_type'], '<=', $end);
            } elseif ($condition == '-30') {
                $start = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month')) . ' 00:00:00';
                $end = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 day')) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
                $db2->where($get['time_type'], '>=', $start);
                $db2->where($get['time_type'], '<=', $end);
            } elseif ($condition == 'time') {
                $time_type = $get['time_type'];
                if (isset($get['last_date']) && $get['last_date'] !== '') {
                    list($start, $end) = explode('-', str_replace('+', '', $get['last_date']));
                    $db->whereBetween($time_type, ["{$start} 00:00:00", "{$end} 23:59:59"]);
                    $db2->whereBetween($time_type, ["{$start} 00:00:00", "{$end} 23:59:59"]);
                }
            }


        }
        if (isset($get['remark_time']) && $get['remark_time'] !== '') {
            $day = $get['remark_time'];
            $start = date("Y-m-d", strtotime("-$day day")) . ' 00:00:00';
            $db->where('remark_time', '<', $start);

        }
        if (isset($get['loan_limit']) && $get['loan_limit'] !== '') {
            $condition = $get['loan_limit'];

            if ($condition == '1') {


                $db->where('money_demand', '>=', 10000);
                $db->where('money_demand', '<=', 50000);
                $db2->where('money_demand', '>=', 10000);
                $db2->where('money_demand', '<=', 50000);
            } elseif ($condition == '2') {

                $db->where('money_demand', '>=', 50000);
                $db->where('money_demand', '<=', 100000);
                $db2->where('money_demand', '>=', 50000);
                $db2->where('money_demand', '<=', 100000);
            } elseif ($condition == '3') {
                $db->where('money_demand', '>=', 100000);
                $db->where('money_demand', '<=', 200000);
                $db2->where('money_demand', '>=', 100000);
                $db2->where('money_demand', '<=', 200000);
            } elseif ($condition == '4') {
                $minimum = $get['minimum'];
                $highest = $get['highest'];
                $db->where('money_demand', '>=', $minimum);
                $db->where('money_demand', '<=', $highest);
                $db2->where('money_demand', '>=', $minimum);
                $db2->where('money_demand', '<=', $highest);
            }


        }
        foreach (['department_id', 'file_id', 'user_id', 'customer_type_id', 'status', 'single_piece_type', 'sex', 'new_data'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, '=', "$get[$key]");
            (isset($get[$key]) && $get[$key] !== '') && $db2->where($key, '=', "$get[$key]");
        }
        foreach (['remarks'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%$get[$key]%");
            }
        }
        // if($user['department_id']!=1){
        $db->where('user_id', $user['id']);
        $db2->where('user_id', $user['id']);
        // }
        if (isset($get['is_history']) && $get['is_history'] != '') {

            $file_list = Db::name("CrmFile")->where('type', 2)->field('id')->select();
            $t = '';
            foreach ($file_list as $v) {
                $v = join(",", $v); // 可以用implode将一维数组转换为用逗号连接的字符串，join是别名
                $temp[] = $v;
            }
            $file_list = implode(',', $temp);
            if ($get['is_history'] == 1) {
                $db->whereIn('file_id', $file_list);
                $db2->whereIn('file_id', $file_list);
            } else {
                $db->whereNotIn('file_id', $file_list);
                $db2->whereNotIn('file_id', $file_list);
            }

        }
        if (isset($get['is_follow']) && $get['is_follow'] != '') {


            if ($get['is_follow'] == 1) {
                $db->whereNull('remark_time');

            } else {
                $db->whereNotNull('remark_time');
            }

        }
        $db->whereNull('is_rubbish');
        $db->whereNull('is_public');
        $db->whereNull('is_quit');
        $db->where('is_deleted', 0);
        $db2->whereNull('is_rubbish');
        $db2->whereNull('is_public');
        $db2->whereNull('is_quit');
        $db2->where('is_deleted', 0);
        $department_id = $user['department_id'];
        $this->assign('department_id', $department_id);

        session('xygsql', $db2->buildSql());


        $dgjsl = Db::query("SELECT count(*)as num from crm_customer where `status`=1 and is_reassign is null   AND `is_deleted` = 0 and user_id='$user_id' and DATE_SUB(CURDATE(), INTERVAL 1 DAY) >= date(last_time)");
        $this->assign('dgjsl', $dgjsl[0]['num']);
        $srdgjsl = Db::query("SELECT count(*)as num from crm_customer where `status`=1 and is_reassign is null  AND `is_deleted` = 0 and user_id='$user_id' and DATE_SUB(CURDATE(), INTERVAL 3 DAY) >= date(last_time)");
        $this->assign('srdgjsl', $srdgjsl[0]['num']);
        $wgjsj = Db::query("SELECT count(*)as num from crm_customer where     user_id='$user_id' and is_reassign is null AND `is_deleted` = 0  and DATE_SUB(CURDATE(), INTERVAL 10 DAY) >= date(last_time)");
        $this->assign('wgjsj', $wgjsj[0]['num']);
        $exwgjsj = Db::query("SELECT count(*)as num from crm_customer where  user_id='$user_id' and is_reassign is null AND `is_deleted` = 0  and DATE_SUB(CURDATE(), INTERVAL 10 DAY) >= date(last_time) and customer_type_id>2 and customer_type_id<=5");
        $this->assign('exwgjsj', $exwgjsj[0]['num']);
        $ebwgjsj = Db::query("SELECT count(*)as num from crm_customer where    user_id='$user_id' and is_reassign is null AND `is_deleted` = 0  and DATE_SUB(CURDATE(), INTERVAL 30 DAY) >= date(last_time)  ");
        $this->assign('ebwgjsj', $ebwgjsj[0]['num']);
        $exebwgjsj = Db::query("SELECT count(*)as num from crm_customer where    user_id='$user_id' and is_reassign is null AND `is_deleted` = 0  and DATE_SUB(CURDATE(), INTERVAL 30 DAY) >= date(last_time) and customer_type_id>2 and customer_type_id<=5");
        $this->assign('exebwgjsj', $exebwgjsj[0]['num']);
        if (isset($get['sjzt']) && $get['sjzt'] !== "") {
            $day = $get['sjzt'];


            $sj = date("Y-m-d 23:59:59", strtotime("-$day day"));
            $db->where('last_time', '<=', $sj);

        }
        if (isset($get['custype']) && $get['custype'] !== "") {

            $db->where('customer_type_id', '>=', 2)->where('customer_type_id', '<=', 5);

        }
        return parent::_list($db);

    }

    public function index3()
    {
        $this->title = '再分配客户';
        $user = session('user');
        $user_id = $user['id'];
        $get = $this->request->get();

        if (isset($get['is_reassign']) && $get['is_reassign'] == 'is_reassign' && isset($get['my']) && $get['my'] == 'my') {
            $db = Db::name($this->table);//->where('status', '<>', 0);
            $db2 = Db::name($this->table);//->where('status', '<>', 0);
            $this->assign('my', 'my');
            $this->assign('is_reassign', 'is_reassign');
        } else if (isset($get['is_reassign']) && $get['is_reassign'] == 'is_reassign') {
            $db = Db::name($this->table)->where('is_reassign', '1');
            $db2 = Db::name($this->table)->where('is_reassign', '1');
            $this->assign('my', '0');
            $this->assign('is_reassign', 'is_reassign');
        } else if (isset($get['my']) && $get['my'] == 'my') {
            $db = Db::name($this->table)->where('is_reassign', 'null');
            $db2 = Db::name($this->table)->where('is_reassign', 'null');
            $this->assign('is_reassign', '0');
            $this->assign('my', 'my');
        } else {
            $db = Db::name($this->table)->where('is_reassign', '1');
            $db2 = Db::name($this->table)->where('is_reassign', '1');
            $this->assign('my', 'my');
            $this->assign('is_reassign', 'is_reassign');
        }
        $sort = Cache::store('redis')->get('index3' . "_$user_id");

        if (isset($get['last_time']) && $get['last_time'] !== '') {
            $condition = $get['last_time'];
            /*if($condition == '-1'){
                $end=date("Y-m-d",strtotime("-1 day")).' 23:59:59';
                $db->where($get['last_time'], '<=',$end);
            }elseif*/
            switch ($condition) {
                case "-1":
                    $end = date("Y-m-d", strtotime("-1 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-2":
                    $end = date("Y-m-d", strtotime("-2 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-3":
                    $end = date("Y-m-d", strtotime("-3 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-4":
                    $end = date("Y-m-d", strtotime("-4 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-5":
                    $end = date("Y-m-d", strtotime("-5 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-6":
                    $end = date("Y-m-d", strtotime("-6 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-7":
                    $end = date("Y-m-d", strtotime("-7 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-8":
                    $end = date("Y-m-d", strtotime("-8 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-9":
                    $end = date("Y-m-d", strtotime("-9 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-10":
                    $end = date("Y-m-d", strtotime("-10 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-11":
                    $end = date("Y-m-d", strtotime("-11 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-12":
                    $end = date("Y-m-d", strtotime("-12 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-13":
                    $end = date("Y-m-d", strtotime("-13 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-14":
                    $end = date("Y-m-d", strtotime("-14 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-15":
                    $end = date("Y-m-d", strtotime("-15 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
            }
        }
        //$db = Db::name($this->table)->where('status','<>',0)->where('status','<>',6)->where('is_reassign','null')->order('field(`status`,0) desc ')->order(' field(`customer_type_id`,\'5\',\'4\',\'3\',\'2\',\'1\',\'6\') ')->order('create_time desc');
        if ($sort == false) {
            // $db->order('is_read,comment desc')->order('new_data');
            //$db2->order('is_read,comment desc')->order('new_data');
        } else {
            $order = explode(',', $sort['order']);

            foreach ($order as $vo) {
                if ($vo == 'customer_type_id') {
                    $customer_type_id_sort = $sort['customer_type_id'];
                    $customer_type_id = "field(`customer_type_id`,$customer_type_id_sort)" . ' desc';
                    $db->order("$customer_type_id");
                    $db2->order("$customer_type_id");
                } elseif ($vo == 'status') {
                    $status_sort = $sort['status'];
                    $status = "field(`status`,$status_sort)" . ' desc';
                    $db->order("$status");
                    $db2->order("$status");
                } elseif ($vo == 'new_data') {
                    $new_data = "field(`new_data`,1)" . ' desc';
                    $db->order("$new_data");
                    $db2->order("$new_data");
                } elseif ($vo == 'is_read') {
                    $db->order('is_read,comment desc');
                    $db2->order('is_read,comment desc');
                } else {
                    $db->order($vo . ' ' . 'desc');
                    $db2->order($vo . ' ' . 'desc');
                }

            }


        }

        if (isset($get['customer_content']) && $get['customer_content'] !== '') {
            $customer_content = $get['customer_content'];

            $db->where($get['customer_condition'], 'like', "%$customer_content%");
            $db2->where($get['customer_condition'], 'like', "%$customer_content%");
        }
        if (isset($get['condition']) && $get['condition'] !== '') {
            $condition = $get['condition'];

            $db->where($get['loan_conditions'], $condition);
            $db2->where($get['loan_conditions'], $condition);
        }
        if (isset($get['remark_time']) && $get['remark_time'] !== '') {
            $day = $get['remark_time'];
            $start = date("Y-m-d", strtotime("-$day day")) . ' 00:00:00';
            $db->where('remark_time', '<', $start);
            $db2->where('remark_time', '<', $start);
        }
        if (isset($get['remark_times_condition']) && $get['remark_times_condition'] !== '' && isset($get['remark_times']) && $get['remark_times'] !== '') {
            $remark_times = $get['remark_times'];
            $remark_times_condition = html_entity_decode(urldecode($get['remark_times_condition']));
            $db->where('remark_times', $remark_times_condition, $remark_times);
            $db2->where('remark_times', $remark_times_condition, $remark_times);
        }
        if (isset($get['time_content']) && $get['time_content'] !== '') {
            $condition = $get['time_content'];

            if ($condition == '1') {
                $start = date("Y/m/d") . ' 00:00:00';
                $db->where($get['time_type'], '>=', $start);
                $db2->where($get['time_type'], '>=', $start);
            } elseif ($condition == '-1') {
                $start = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';
                $end = date("Y-m-d", strtotime("-1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
                $db2->where($get['time_type'], '>=', $start);
                $db2->where($get['time_type'], '<=', $end);
            } elseif ($condition == '7') {
                $start = date("Y-m-d", strtotime("-7 day")) . ' 00:00:00';
                $end = date("Y/m/d") . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
                $db2->where($get['time_type'], '>=', $start);
                $db2->where($get['time_type'], '<=', $end);
            } elseif ($condition == '30') {
                $start = date('Y-m-01', strtotime(date("Y-m-d"))) . ' 00:00:00';
                $end = date('Y-m-d', strtotime("$start +1 month -1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
                $db2->where($get['time_type'], '>=', $start);
                $db2->where($get['time_type'], '<=', $end);
            } elseif ($condition == '-30') {
                $start = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month')) . ' 00:00:00';
                $end = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 day')) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
                $db2->where($get['time_type'], '>=', $start);
                $db2->where($get['time_type'], '<=', $end);
            } elseif ($condition == 'time') {
                $time_type = $get['time_type'];
                if (isset($get['last_date']) && $get['last_date'] !== '') {
                    list($start, $end) = explode('-', str_replace('+', '', $get['last_date']));
                    $db->whereBetween($time_type, ["{$start} 00:00:00", "{$end} 23:59:59"]);
                    $db2->whereBetween($time_type, ["{$start} 00:00:00", "{$end} 23:59:59"]);
                }
            }


        }
        if (isset($get['remark_time']) && $get['remark_time'] !== '') {
            $day = $get['remark_time'];
            $start = date("Y-m-d", strtotime("-$day day")) . ' 00:00:00';
            $db->where('remark_time', '<', $start);

        }
        if (isset($get['loan_limit']) && $get['loan_limit'] !== '') {
            $condition = $get['loan_limit'];

            if ($condition == '1') {


                $db->where('money_demand', '>=', 10000);
                $db->where('money_demand', '<=', 50000);
                $db2->where('money_demand', '>=', 10000);
                $db2->where('money_demand', '<=', 50000);
            } elseif ($condition == '2') {

                $db->where('money_demand', '>=', 50000);
                $db->where('money_demand', '<=', 100000);
                $db2->where('money_demand', '>=', 50000);
                $db2->where('money_demand', '<=', 100000);
            } elseif ($condition == '3') {
                $db->where('money_demand', '>=', 100000);
                $db->where('money_demand', '<=', 200000);
                $db2->where('money_demand', '>=', 100000);
                $db2->where('money_demand', '<=', 200000);
            } elseif ($condition == '4') {
                $minimum = $get['minimum'];
                $highest = $get['highest'];
                $db->where('money_demand', '>=', $minimum);
                $db->where('money_demand', '<=', $highest);
                $db2->where('money_demand', '>=', $minimum);
                $db2->where('money_demand', '<=', $highest);
            }


        }
        foreach (['department_id', 'file_id', 'user_id', 'customer_type_id', 'status', 'single_piece_type', 'sex'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, '=', "$get[$key]");
            (isset($get[$key]) && $get[$key] !== '') && $db2->where($key, '=', "$get[$key]");
        }

        //if($user['department_id']!=1){
        $db->where('user_id', $user['id']);
        $db2->where('user_id', $user['id']);
        //}
        foreach (['remarks'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%$get[$key]%");
            }
        }
        if (isset($get['is_history']) && $get['is_history'] != '') {

            $file_list = Db::name("CrmFile")->where('type', 2)->field('id')->select();

            if ($get['is_history'] == 1) {
                $db->whereIn('file_id', $file_list[0]);
                $db2->whereIn('file_id', $file_list[0]);
            } else {
                $db->whereNotIn('file_id', $file_list[0]);
                $db2->whereNotIn('file_id', $file_list[0]);
            }

        }
        if (isset($get['is_follow']) && $get['is_follow'] != '') {


            if ($get['is_follow'] == 1) {
                $db->whereNull('remark_time');

            } else {
                $db->whereNotNull('remark_time');
            }
        }
        $db->whereNull('is_rubbish');
        $db->whereNull('is_public');
        $db->whereNull('is_quit');
        $db->where('is_deleted', 0);
        $db2->whereNull('is_rubbish');
        $db2->whereNull('is_public');
        $db2->whereNull('is_quit');
        $db2->where('is_deleted', 0);
        $department_id = $user['department_id'];
        $this->assign('department_id', $department_id);


        //“待跟进”客户超过1天未跟进
        $dgj1 = Db::query("SELECT count(*)as num from crm_customer where `status`=1 and is_reassign=1   AND `is_deleted` = 0 and user_id='$user_id' and CONCAT(DATE_SUB(CURDATE(), INTERVAL 1 DAY),' 23:59:59') >= date(last_time)");
        $this->assign('dgj1', $dgj1[0]['num']);
        //2-3星“待签约”客户超过9天未跟进
        $dqy9_2_3 = Db::query("SELECT count(*)as num from crm_customer where `status`=3 and customer_type_id between 2 and 3 and is_reassign=1  AND `is_deleted` = 0 and user_id='$user_id' and CONCAT(DATE_SUB(CURDATE(), INTERVAL 9 DAY),' 23:59:59') >= date(last_time)");
        $this->assign('dqy9_2_3', $dqy9_2_3[0]['num']);

        //超过10天未跟进
        $dgj10 = Db::query("SELECT count(*)as num from crm_customer where `status` <> 0 and  user_id='$user_id' and is_reassign=1 AND `is_deleted` = 0  and CONCAT(DATE_SUB(CURDATE(), INTERVAL 10 DAY),' 23:59:59') >= date(last_time)");
        $this->assign('dgj10', $dgj10[0]['num']);

        //超过10天未跟进的三星以上
        $dgj10_3 = Db::query("SELECT count(*)as num from crm_customer where  `status` <> 0 and   user_id='$user_id' and is_reassign=1 and customer_type_id >=3 AND `is_deleted` = 0  and CONCAT(DATE_SUB(CURDATE(), INTERVAL 10 DAY),' 23:59:59') >= date(last_time)");
        $this->assign('dgj10_3', $dgj10_3[0]['num']);

        //超过29天未跟进
        $dgj29 = Db::query("SELECT count(*)as num from crm_customer where  `status` <> 0 and   user_id='$user_id' and is_reassign=1 AND `is_deleted` = 0  and CONCAT(DATE_SUB(CURDATE(), INTERVAL 29 DAY),' 23:59:59') >= date(last_time)");
        $this->assign('dgj29', $dgj29[0]['num']);

        //超过29天未跟进的三星以上
        $dgj29_3 = Db::query("SELECT count(*)as num from crm_customer where  `status` <> 0 and   user_id='$user_id' and is_reassign=1 and customer_type_id >=3 AND `is_deleted` = 0  and CONCAT(DATE_SUB(CURDATE(), INTERVAL 29 DAY),' 23:59:59') >= date(last_time)");
        $this->assign('dgj29_3', $dgj29_3[0]['num']);

        //4星以上超过4天未跟进
        $dgj4_4 = Db::query("SELECT count(*)as num from crm_customer where  `status` <> 0 and   user_id='$user_id' and is_reassign=1 and customer_type_id >=4 AND `is_deleted` = 0  and CONCAT(DATE_SUB(CURDATE(), INTERVAL 4 DAY),' 23:59:59') >= date(last_time)");
        $this->assign('dgj4_4', $dgj4_4[0]['num']);

        if (isset($get['custype_l']) && $get['custype_l'] !== "") {

            $db->where('customer_type_id', '>=', $get['custype_l']);

        }

        if (isset($get['custype_r']) && $get['custype_r'] !== "") {

            $db->where('customer_type_id', '<=', $get['custype_r']);

        }

        if (isset($get['sjzt']) && $get['sjzt'] !== "") {
            $day = $get['sjzt'];


            $sj = date("Y-m-d 23:59:59", strtotime("-$day day"));
            $db->where('last_time', '<=', $sj);

        }
        if (isset($get['custype']) && $get['custype'] !== "") {

            $db->where('customer_type_id', '>=', 2)->where('customer_type_id', '<=', 5);

        }
        session('xygsql', $db2->buildSql());
        return parent::_list($db);
    }

    public function index4()
    {
        $this->title = '离职人员客户';
        $db = Db::name($this->table)->order('create_time desc');
        $get = $this->request->get();
        if (isset($get['customer_content']) && $get['customer_content'] !== '') {
            $customer_content = $get['customer_content'];

            $db->where($get['customer_condition'], 'like', "%$customer_content%");

        }
        if (isset($get['condition']) && $get['condition'] !== '') {
            $condition = $get['condition'];

            $db->where($get['loan_conditions'], $condition);

        }
        if (isset($get['remark_time']) && $get['remark_time'] !== '') {
            $day = $get['remark_time'];
            $start = date("Y-m-d", strtotime("-$day day")) . ' 00:00:00';
            $db->where('remark_time', '<', $start);

        }
        if (isset($get['time_content']) && $get['time_content'] !== '') {
            $condition = $get['time_content'];

            if ($condition == '1') {
                $start = date("Y/m/d") . ' 00:00:00';
                $db->where($get['time_type'], '>=', $start);
            } elseif ($condition == '-1') {
                $start = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';
                $end = date("Y-m-d", strtotime("-1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '7') {
                $start = date("Y-m-d", strtotime("-7 day")) . ' 00:00:00';
                $end = date("Y/m/d") . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '30') {
                $start = date('Y-m-01', strtotime(date("Y-m-d"))) . ' 00:00:00';
                $end = date('Y-m-d', strtotime("$start +1 month -1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '-30') {
                $start = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month')) . ' 00:00:00';
                $end = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 day')) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == 'time') {
                $time_type = $get['time_type'];
                if (isset($get['last_date']) && $get['last_date'] !== '') {
                    list($start, $end) = explode('-', str_replace('+', '', $get['last_date']));
                    $db->whereBetween($time_type, ["{$start} 00:00:00", "{$end} 23:59:59"]);
                }
            }


        }
        foreach (['file_id', 'customer_type_id', 'status'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, '=', "$get[$key]");
        }
        foreach (['remarks'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%$get[$key]%");
            }
        }
        if (isset($get['department_id1']) && $get['department_id1'] != '') {
            $department_id = $get['department_id1'];

            if (isset($get['department_id2']) && $get['department_id2'] != '') {
                $department_id = $get['department_id2'];

                if (isset($get['department_id3']) && $get['department_id3'] != '') {
                    $department_id = $get['department_id3'];

                }
            }
            $department_list = Cache::store('redis')->get('department_tree' . $department_id);
            $db->where('department_id', 'in', $department_list);
        }


        $user = session('user');
        $department_id = $user['department_id'];
        $list = Db::query("SELECT * from department where pid=$department_id");
        if ($department_id == 1) {

        } else {


            if ($list == null) {


                $db->where('department_id', $user['department_id']);

            } else {

                $comma_separated = "";
                $department_list = Cache::store('redis')->get('operation_departemnt' . $user['id']);
                foreach ($department_list as $department) {
                    $comma_separated = $comma_separated . $department['id'] . ',';
                }
                $comma_separated = substr($comma_separated, 0, strlen($comma_separated) - 1);
                $db->where('department_id', 'in', $comma_separated);

            }

        }
        $user_list300 = Cache::store('redis')->get("department_user" . $user['department_id']);

        $id_list = '';
        foreach ($user_list300 as $vo1) {
            $id_list = $id_list . $vo1['id'] . ',';
        }
        $id_list = substr($id_list, 0, strlen($id_list) - 1);
        // var_dump($id_list);exit
        $company = Db::name("Department")->find($user['company_id']);
        if ($company['is_limit'] == 1) {
            $user300_list = Db::query("SELECT id,name,nums from (select  t2.*,SUM(IF(num is null  , 0, num)) as nums     from (SELECT *  from  system_user  LEFT JOIN (SELECT user_id,count(*) as num      from  crm_customer   GROUP BY user_id) t1 on system_user.id=t1.user_id   where system_user.id in ($id_list)  GROUP BY id ) t2    GROUP BY id )t3 where t3.nums<300 and t3.is_deleted!=1");
        } else {
            $user300_list = Db::query("SELECT id,name,nums from (select  t2.*,0 as nums     from (SELECT *  from  system_user  LEFT JOIN (SELECT user_id,count(*) as num      from  crm_customer   GROUP BY user_id) t1 on system_user.id=t1.user_id   where system_user.id in ($id_list)  GROUP BY id ) t2    GROUP BY id )t3 where t3.nums<300 and t3.is_deleted!=1");

        }
        //$user300_list=Cache::store('redis')->get('department_user'.$user['department_id']);

        $this->assign('user300_list', $user300_list);
        $this->assign('department_id', $department_id);
        $db->where('is_quit', 1);
        $db->whereNull('is_rubbish');
        $db->whereNull('is_public');
        return parent::_list($db);
    }

    public function index5()
    {
        $this->title = '待分配客户管理';
        $user = session('user');
        $department_id = $user['department_id'];
        //$db = Db::name($this->table)->alias('a')->join('statistics w','a.id <> w.customer_id')->where('a.status',9)->order('a.create_time desc');
        $db = Db::name($this->table)->whereIn('status', [9]);
        if ($user['authorize'] == 17) {
            $db->whereIn('department_id', $user['manage_company']);
        }


        $db->whereNull('is_rubbish');
        $db->whereNull('is_public');
        $db->whereNull('is_quit');
        $db->where('is_deleted', 0);
        $db->where('department_id', '<>', 204);
        $db->where('department_id', '<>', 557);
        $db->where('department_id', '<>', 558);
        $db->where('department_id', '<>', 562);
        $db->where('department_id', '<>', 563);
        $db->where('department_id', '<>', 564);
        $db->where('department_id', '<>', 565);
        $db->where('department_id', '<>', 66);
        $db->where('department_id', '<>', 576);
        $db->where('department_id', '<>', 584);
        $db->where('department_id', '<>', 609);
        $db->order('create_time desc');

        $get = $this->request->get();
        foreach (['city'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $value = trim($get[$key]);
                $db->where($key, 'like', "%$value%");
            }
        }
        foreach (['remarks'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%$get[$key]%");
            }
        }
        if (isset($get['customer_content']) && $get['customer_content'] !== '') {
            $customer_content = $get['customer_content'];

            $db->where($get['customer_condition'], 'like', "%$customer_content%");

        }
        if (isset($get['condition']) && $get['condition'] !== '') {
            $condition = $get['condition'];

            $db->where($get['loan_conditions'], $condition);

        }
        if (isset($get['remark_time']) && $get['remark_time'] !== '') {
            $day = $get['remark_time'];
            $start = date("Y-m-d", strtotime("-$day day")) . ' 00:00:00';
            $db->where('remark_time', '<', $start);

        }
        if (isset($get['time_content']) && $get['time_content'] !== '') {
            $condition = $get['time_content'];

            if ($condition == '1') {
                $start = date("Y/m/d") . ' 00:00:00';
                $db->where($get['time_type'], '>=', $start);
            } elseif ($condition == '-1') {
                $start = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';
                $end = date("Y-m-d", strtotime("-1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '7') {
                $start = date("Y-m-d", strtotime("-7 day")) . ' 00:00:00';
                $end = date("Y/m/d") . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '30') {
                $start = date('Y-m-01', strtotime(date("Y-m-d"))) . ' 00:00:00';
                $end = date('Y-m-d', strtotime("$start +1 month -1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '-30') {
                $start = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month')) . ' 00:00:00';
                $end = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 day')) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == 'time') {
                $time_type = $get['time_type'];
                if (isset($get['last_date']) && $get['last_date'] !== '') {
                    list($start, $end) = explode('-', str_replace('+', '', $get['last_date']));
                    $db->whereBetween($time_type, ["{$start} 00:00:00", "{$end} 23:59:59"]);
                }
            }
        }

        foreach (['file_id', 'customer_type_id', 'department_id'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, '=', "$get[$key]");
        }

        $this->assign('department_id', $department_id);
        return parent::_list($db);
    }

    public function index8()
    {
        $this->title = '分公司待分配客户管理';
        $user = session('user');
        $department_id = $user['department_id'];

        //  $db = Db::name($this->table)->alias('a')->join('statistics w','a.id <> w.customer_id')->where('a.status',9)->order('a.create_time desc');
        $db = Db::name($this->table)->where('status', 9);
        $db->whereNull('is_rubbish');
        $db->whereNull('is_public');
        $db->whereNull('is_quit');
        $db->where('is_deleted', 0);
        $db->where('department_id', '<>', 204);
        $db->where('department_id', '<>', 557);
        $db->where('department_id', '<>', 558);
        $db->where('department_id', '<>', 562);
        $db->where('department_id', '<>', 563);
        $db->where('department_id', '<>', 564);
        $db->where('department_id', '<>', 565);
        $db->where('department_id', '<>', 66);
        $db->where('department_id', '<>', 576);
        $db->where('department_id', '<>', 584);
        $db->where('department_id', '<>', 609);
        $db->order('create_time desc');
        //如果不是普江集团权限只能看到自己机构下的待分配数据
        if ($department_id != 1) {
            if ($user['authorize'] == 17) {
                $db->whereIn('department_id', $user['manage_company']);
            } else {
                $db->where('department_id', $department_id);
            }
        }

        $get = $this->request->get();
        if (isset($get['customer_content']) && $get['customer_content'] !== '') {
            $customer_content = $get['customer_content'];

            $db->where($get['customer_condition'], 'like', "%$customer_content%");

        }
        foreach (['department_id', 'file_id', 'user_id', 'customer_type_id', 'status'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, '=', "$get[$key]");
        }
        if (isset($get['time_content']) && $get['time_content'] !== '') {
            $condition = $get['time_content'];

            if ($condition == '1') {
                $start = date("Y/m/d") . ' 00:00:00';
                $db->where($get['time_type'], '>=', $start);
            } elseif ($condition == '-1') {
                $start = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';
                $end = date("Y-m-d", strtotime("-1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '7') {
                $start = date("Y-m-d", strtotime("-7 day")) . ' 00:00:00';
                $end = date("Y/m/d") . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '30') {
                $start = date('Y-m-01', strtotime(date("Y-m-d"))) . ' 00:00:00';
                $end = date('Y-m-d', strtotime("$start +1 month -1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '-30') {
                $start = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month')) . ' 00:00:00';
                $end = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 day')) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == 'time') {
                $time_type = $get['time_type'];
                if (isset($get['last_date']) && $get['last_date'] !== '') {
                    list($start, $end) = explode('-', str_replace('+', '', $get['last_date']));
                    $db->whereBetween($time_type, ["{$start} 00:00:00", "{$end} 23:59:59"]);
                }
            }


        }


        $this->assign('department_id', $department_id);

        return parent::_list($db);
    }

    public function index6()
    {
        $this->title = '无效客户管理';
        $user = session('user');
        $department_id = $user['department_id'];

        //  $db = Db::name($this->table)->alias('a')->join('statistics w','a.id <> w.customer_id')->where('a.status',9)->order('a.create_time desc');
        $db = Db::name($this->table);
        $db->where('is_rubbish is not null');
        $db->whereNull('is_public');
        $db->whereNull('is_quit');
        $db->where('is_deleted', 0);
        $get = $this->request->get();
        if (isset($get['customer_content']) && $get['customer_content'] !== '') {
            $customer_content = $get['customer_content'];

            $db->where($get['customer_condition'], 'like', "%$customer_content%");

        }
        if (isset($get['condition']) && $get['condition'] !== '') {
            $condition = $get['condition'];

            $db->where($get['loan_conditions'], $condition);

        }
        if (isset($get['remark_time']) && $get['remark_time'] !== '') {
            $day = $get['remark_time'];
            $start = date("Y-m-d", strtotime("-$day day")) . ' 00:00:00';
            $db->where('remark_time', '<', $start);

        }
        if (isset($get['time_content']) && $get['time_content'] !== '') {
            $condition = $get['time_content'];

            if ($condition == '1') {
                $start = date("Y/m/d") . ' 00:00:00';
                $db->where($get['time_type'], '>=', $start);
            } elseif ($condition == '-1') {
                $start = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';
                $end = date("Y-m-d", strtotime("-1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '7') {
                $start = date("Y-m-d", strtotime("-7 day")) . ' 00:00:00';
                $end = date("Y/m/d") . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '30') {
                $start = date('Y-m-01', strtotime(date("Y-m-d"))) . ' 00:00:00';
                $end = date('Y-m-d', strtotime("$start +1 month -1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '-30') {
                $start = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month')) . ' 00:00:00';
                $end = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 day')) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == 'time') {
                $time_type = $get['time_type'];
                if (isset($get['last_date']) && $get['last_date'] !== '') {
                    list($start, $end) = explode('-', str_replace('+', '', $get['last_date']));
                    $db->whereBetween($time_type, ["{$start} 00:00:00", "{$end} 23:59:59"]);
                }
            }


        }
        foreach (['file_id', 'customer_type_id'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, '=', "$get[$key]");
        }
        if (isset($get['department_id1']) && $get['department_id1'] != '') {
            $department_id = $get['department_id1'];

            if (isset($get['department_id2']) && $get['department_id2'] != '') {
                $department_id = $get['department_id2'];

                if (isset($get['department_id3']) && $get['department_id3'] != '') {
                    $department_id = $get['department_id3'];

                }
            }
            $department_list = Cache::store('redis')->get('department_tree' . $department_id);
            $db->where('department_id', 'in', $department_list);
        }


        $user = session('user');
        $department_id = $user['department_id'];
        $list = Db::query("SELECT * from department where pid=$department_id");
        if ($department_id == 1) {

        } else {


            if ($list == null) {


                $db->where('department_id', $user['department_id']);

            } else {

                $comma_separated = "";
                $department_list = Cache::store('redis')->get('operation_departemnt' . $user['id']);
                foreach ($department_list as $department) {
                    $comma_separated = $comma_separated . $department['id'] . ',';
                }
                $comma_separated = substr($comma_separated, 0, strlen($comma_separated) - 1);
                $db->where('department_id', 'in', $comma_separated);

            }

        }
        $user_list300 = Cache::store('redis')->get("department_user" . $user['department_id']);

        $id_list = '';
        foreach ($user_list300 as $vo1) {
            $id_list = $id_list . $vo1['id'] . ',';
        }
        $id_list = substr($id_list, 0, strlen($id_list) - 1);

        $company = Db::name("Department")->find($user['company_id']);
        if ($company['is_limit'] == 1) {
            $user300_list = Db::query("SELECT id,name,nums from (select  t2.*,SUM(IF(num is null  , 0, num)) as nums     from (SELECT *  from  system_user  LEFT JOIN (SELECT user_id,count(*) as num      from  crm_customer   GROUP BY user_id) t1 on system_user.id=t1.user_id   where system_user.id in ($id_list)  GROUP BY id ) t2    GROUP BY id )t3 where t3.nums<9999 and t3.is_deleted!=1");
        } else {
            $user300_list = Db::query("SELECT id,name,nums from (select  t2.*,0 as nums     from (SELECT *  from  system_user  LEFT JOIN (SELECT user_id,count(*) as num      from  crm_customer   GROUP BY user_id) t1 on system_user.id=t1.user_id   where system_user.id in ($id_list)  GROUP BY id ) t2    GROUP BY id )t3 where t3.nums<9999 and t3.is_deleted!=1");

        }
        $this->assign('user300_list', $user300_list);
        return parent::_list($db);
    }

    public function index7()
    {
        $this->title = '客户查询管理';
        $user = session('user');
        $db = Db::name($this->table);
        $get = $this->request->get();
        foreach (['name', 'mobile', 'id'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {

                $value = trim($get[$key]);
                $db->where($key, '=', "$value");
            }
        }
        if (isset($get['mobile']))//是否存在"id"的参数
        {
            if ($get['mobile'] == '' and $get['name'] == '' and $get['id'] == '') {
                $db->where('id is null');
            }
        } elseif (isset($get['name'])) {
            if ($get['name'] == '' and $get['mobile'] == '' and $get['id'] == '') {
                $db->where('id is null');
            }
        } elseif (isset($get['id'])) {
            if ($get['name'] == '' and $get['mobile'] == '' and $get['id'] == '') {
                $db->where('id is null');
            }
        } else {

            $db->where('id is null');


        }
        $department_list = Cache::store('redis')->get('department_tree' . $user['company_id']);
        $db->where('department_id', 'in', $department_list);

        $db->where('is_deleted', 0);
        $department_id = $user['department_id'];
        $this->assign('department_id', $department_id);
        $this->assign('user_id', $user['id']);


        return parent::_list($db);
    }


    public function public_pool()
    {
        $this->title = '公共池';
        $user = session('user');
        $auth = [1, 11];

        if ($user['authorize'] == 2) {

            $hi = date('H:i:s');
            if ($hi > '22:30:00') {
                $this->error('访问时间已超时!', url('customer/customer/index2'));
            } else {
                if ($hi < '08:20:00') {
                    $this->error('时间未到!', url('customer/customer/index2'));
                }
            }
        }
        // if($user['authorize'] != 1){
        //     $this->error('暂时关闭!', url('customer/customer/index2'));
        // }
        $db = Db::name($this->table);
        $db->whereNull('is_rubbish');
        $db->where('is_public', 1);
        $db->whereNull('is_quit');
        $db->where('is_deleted', 0)->order('last_time desc');
        $get = $this->request->get();
        $user_id = $user['id'];
        if ($user['authorize'] == 1 || $user['authorize'] == 11) {
            $this->assign('isdel', '1');
        } else {
            $this->assign('isdel', '0');
        }

        if (isset($get['last_time']) && $get['last_time'] !== '') {
            $condition = $get['last_time'];
            /*if($condition == '-1'){
                $end=date("Y-m-d",strtotime("-1 day")).' 23:59:59';
                $db->where($get['last_time'], '<=',$end);
            }elseif*/
            switch ($condition) {
                case "-1":
                    $end = date("Y-m-d", strtotime("-1 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-2":
                    $end = date("Y-m-d", strtotime("-2 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-3":
                    $end = date("Y-m-d", strtotime("-3 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-4":
                    $end = date("Y-m-d", strtotime("-4 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-5":
                    $end = date("Y-m-d", strtotime("-5 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-6":
                    $end = date("Y-m-d", strtotime("-6 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-7":
                    $end = date("Y-m-d", strtotime("-7 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-8":
                    $end = date("Y-m-d", strtotime("-8 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-9":
                    $end = date("Y-m-d", strtotime("-9 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-10":
                    $end = date("Y-m-d", strtotime("-10 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-11":
                    $end = date("Y-m-d", strtotime("-11 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-12":
                    $end = date("Y-m-d", strtotime("-12 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-13":
                    $end = date("Y-m-d", strtotime("-13 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-14":
                    $end = date("Y-m-d", strtotime("-14 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
                case "-15":
                    $end = date("Y-m-d", strtotime("-15 day")) . ' 23:59:59';
                    $db->where('last_time', '<=', $end);
                    break;
            }
        }
        if (isset($get['department_id1']) && $get['department_id1'] != '') {
            $department_id = $get['department_id1'];
            if (isset($get['department_id2']) && $get['department_id2'] != '') {
                $department_id = $get['department_id2'];

                if (isset($get['department_id3']) && $get['department_id3'] != '') {
                    $department_id = $get['department_id3'];
                }
            }
            $department_list = Cache::store('redis')->get('department_tree' . $department_id);
            $db->where('department_id', 'in', $department_list);
        }
        if (isset($get['customer_content']) && $get['customer_content'] !== '') {
            $customer_content = $get['customer_content'];
            $db->where($get['customer_condition'], 'like', "%$customer_content%");
        }
        if (isset($get['condition']) && $get['condition'] !== '') {
            $condition = $get['condition'];

            $db->where($get['loan_conditions'], $condition);

        }
        if (isset($get['remark_time']) && $get['remark_time'] !== '') {
            $day = $get['remark_time'];
            $start = date("Y-m-d", strtotime("-$day day")) . ' 00:00:00';
            $db->where('remark_time', '<', $start);

        }
        if (isset($get['time_content']) && $get['time_content'] !== '') {
            $condition = $get['time_content'];

            if ($condition == '1') {
                $start = date("Y/m/d") . ' 00:00:00';
                $db->where($get['time_type'], '>=', $start);
            } elseif ($condition == '-1') {
                $start = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';
                $end = date("Y-m-d", strtotime("-1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '7') {
                $start = date("Y-m-d", strtotime("-7 day")) . ' 00:00:00';
                $end = date("Y/m/d") . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '30') {
                $start = date('Y-m-01', strtotime(date("Y-m-d"))) . ' 00:00:00';
                $end = date('Y-m-d', strtotime("$start +1 month -1 day")) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == '-30') {
                $start = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month')) . ' 00:00:00';
                $end = date('Y-m-d', strtotime(date('Y-m-01') . ' -1 day')) . ' 23:59:59';
                $db->where($get['time_type'], '>=', $start);
                $db->where($get['time_type'], '<=', $end);
            } elseif ($condition == 'time') {
                $time_type = $get['time_type'];
                if (isset($get['last_date']) && $get['last_date'] !== '') {
                    list($start, $end) = explode('-', str_replace('+', '', $get['last_date']));
                    $db->whereBetween($time_type, ["{$start} 00:00:00", "{$end} 23:59:59"]);
                }
            }


        }
        if (isset($get['remark_time']) && $get['remark_time'] !== '') {
            $day = $get['remark_time'];
            $start = date("Y-m-d", strtotime("-$day day")) . ' 00:00:00';
            $db->where('remark_time', '<', $start);

        }
        if (isset($get['loan_limit']) && $get['loan_limit'] !== '') {
            $condition = $get['loan_limit'];

            if ($condition == '1') {


                $db->where('money_demand', '>=', 10000);
                $db->where('money_demand', '<=', 50000);
            } elseif ($condition == '2') {

                $db->where('money_demand', '>=', 50000);
                $db->where('money_demand', '<=', 100000);
            } elseif ($condition == '3') {
                $db->where('money_demand', '>=', 100000);
                $db->where('money_demand', '<=', 200000);
            } elseif ($condition == '4') {
                $minimum = $get['minimum'];
                $highest = $get['highest'];
                $db->where('money_demand', '>=', $minimum);
                $db->where('money_demand', '<=', $highest);
            }


        }
        foreach (['department_id', 'file_id', 'user_id', 'customer_type_id', 'status', 'single_piece_type'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, '=', "$get[$key]");
        }
        foreach (['remarks'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%$get[$key]%");
            }
        }
        $department_id = $user['department_id'];
//        $department_id=1;

        if ($department_id == 1) {
            $this->assign('isboss', '1');
        } else {
//            echo $department_id;
            $list = Db::query("SELECT * from department where pid=$department_id");
            if ($list == null) {

                $this->assign('isboss', '0');
                // $db->where('department_id', $user['department_id']);
                $comma_separated = Cache::store('redis')->get('department_tree' . $user['company_id']);
                $db->where('department_id', 'in', $comma_separated);

            } else {
                $this->assign('isboss', '1');
                /*
                $comma_separated="";
                $department_list = Cache::store('redis')->get('operation_departemnt'.$user['id']);
                foreach ($department_list as $department){
                    $comma_separated=$comma_separated.$department['id'].',';
                }
                $comma_separated =  substr($comma_separated,0,strlen($comma_separated)-1);
                */
                $comma_separated = Cache::store('redis')->get('department_tree' . $user['company_id']);
                $db->where('department_id', 'in', $comma_separated);
            }
//            if ($pid == null) {
//
//                $this->assign('isboss', '0');
//                $db->where('department_id', $user['department_id']);
//
//            } else {
//                $this->assign('isboss', '1');
//                /*
//                $comma_separated="";
//                $department_list = Cache::store('redis')->get('operation_departemnt'.$user['id']);
//                foreach ($department_list as $department){
//                    $comma_separated=$comma_separated.$department['id'].',';
//                }
//                $comma_separated =  substr($comma_separated,0,strlen($comma_separated)-1);
//                */
//                $comma_separated = Cache::store('redis')->get('department_tree'.$department_id);
//                $db->where('department_id', 'in', $comma_separated);
//            }
        }
        if (isset($get['is_history']) && $get['is_history'] != '') {
            $file_list = Db::name("CrmFile")->where('type', 2)->field('id')->select();
            $t = '';
            foreach ($file_list as $v) {
                $v = join(",", $v); // 可以用implode将一维数组转换为用逗号连接的字符串，join是别名
                $temp[] = $v;
            }
            $file_list = implode(',', $temp);
            if ($get['is_history'] == 1) {
                $db->whereIn('file_id', $file_list);
            } else {
                $db->whereNotIn('file_id', $file_list);
            }
        }
        if (isset($get['is_follow']) && $get['is_follow'] != '') {
            if ($get['is_follow'] == 1) {
                $db->whereNull('remark_time');
            } else {
                $db->whereNotNull('remark_time');
            }
        }

        $this->assign('department_id', $department_id);
        if (isset($get['sjzt']) && $get['sjzt'] !== "") {
            $day = $get['sjzt'];


            $sj = date("Y-m-d", strtotime("-$day day"));
            $db->where('last_time', '<=', $sj);

        }
        if (isset($get['custype']) && $get['custype'] !== "") {

            $db->where('customer_type_id', '>=', 2)->where('customer_type_id', '<=', 5);

        }

        $this->assign('user_id', $user_id);
        return parent::_list($db);
    }


    /**
     * 列表数据处理
     * @param array $data
     */
    protected function _index5_data_filter(&$data)
    {
        $user = session('user');
        if ($user['id'] == 10012) {

        } else {
            foreach ($data as &$vo) {
                $xing = substr($vo['mobile'], 3, 4);  //获取手机号中间四位
                $vo['mobile'] = str_replace($xing, '****', $vo['mobile']);
            }
        }

        $city_list = Cache::store('redis')->get('city_list');

        if ($user['authorize'] == 17) {
            $allot_department_list = Db::name("department")->whereIn('id', $user['manage_company'])->where('type', 1)->whereNull('is_deleted')->select();
        } else {
            $allot_department_list = Db::name("department")->where('type', 1)->whereNull('is_deleted')->select();
        }
        $department_list = Cache::store('redis')->get('operation_departemnt' . $user['id']);
        $department_list = ToolsService::arr2table($department_list);


        $file_list = Cache::store('redis')->get('file_list');
        $customer_type_list = Cache::store('redis')->get('customer_type_list');
        $this->assign('department_list', $department_list);
        $this->assign('allot_department_list', $allot_department_list);
        $this->assign('city_list', $city_list);
        $this->assign('file_list', $file_list);
        $this->assign('customer_type_list', $customer_type_list);
        $this->assign('num_list', count($data));
        $this->assign('user', $user);
    }

    /**
     * 列表数据处理
     * @param array $data
     */
    protected function _index8_data_filter(&$data)
    {
        $get = $this->request->get();
        $user = session('user');
        foreach ($data as &$vo) {
            if ($user['department_id'] != 1) {
                if ($vo['money_demand'] < 50000) {
                    $vo['money_demand'] = '1万-5万';
                } else if ($vo['money_demand'] <= 100000 && $vo['money_demand'] >= 50000) {
                    $vo['money_demand'] = '5万-10万';
                }
            }
        }
        $department_list = Cache::store('redis')->get('operation_departemnt' . $user['id']);
        $department_list = ToolsService::arr2table($department_list);
        $user = session('user');
        if ($user['department_id'] == 1) {
            $department_list1 = Db::name('Department')->whereNull('is_deleted')->where('pid', 1)->select();

            if (isset($get['department_id1']) && $get['department_id1'] !== "") {
                $department_list2 = Db::name('Department')->whereNull('is_deleted')->where('b::name('Department')->whereNull('is_deleted')->where('pid', $get['department_id1'])->select();
            } else {
                $department_list2 = [];
            }

            if (isset($get['department_id2']) && $get['department_id2'] !== "") {
                $department_list3 = Db::name('Department')->whereNull('is_deleted')->where('pid', $get['department_id2'])->select();
            } else {
                $department_list3 = [];
            }


        }


        //$department_list=ToolsService::arr2table($department_list);

        if (isset($get['department_id1']) && $get['department_id1'] !== "") {
            $department_id = $get['department_id1'];

            if (isset($get['department_id2']) && $get['department_id2'] != '') {
                $department_id = $get['department_id2'];

                if (isset($get['department_id3']) && $get['department_id3'] != '') {
                    $department_id = $get['department_id3'];


                }
            }

            $user_list = b::query("SELECT id,name from (SELECT t2.*,SUM(IF(num is null  , 0, num)) as nums  from (SELECT * from  system_user  LEFT JOIN (SELECT user_id,count(*) as num  from  crm_customer   GROUP BY user_id) t1 on system_user.id=t1.user_id  LEFT JOIN (SELECT user_id as u_id,is_new from user_allot)u1 on system_user.id=u1.u_id  where  system_user.id in ($id_list)   and u1.is_new=1)t2 GROUP BY id )t3 where t3.nums<9999 and t3.is_deleted!=1");

        $this->assign('user300_list', $user300_list);
    }

    /**
     * 列表数据处理
     * @param array $data
     */
    protected function _index6_data_filter(&$data)
    {
        $user = session('user');
        foreach ($data as &$vo) {
            $xing = substr($vo['mobile'], 3, 4);  //获取手机号中间四位
            $vo['mobile'] = str_replace($xing, '****', $vo['mobile']);
        }
        $department_list = Cache::store('redis')->get('operation_departemnt' . $user['id']);
        $department_list = ToolsService::arr2table($department_list);

        $fCache::store('redis')->get('department_user' . $department_id);
        } else {
            $user_list = Cache::store('redis')->get('operation_user' . $user['id']);
        }


        $file_list = Cache::store('redis')->get('file_list');
        $customer_type_list = Cache::store('redis')->get('customer_type_list');
        $this->assign('department_list', $department_list);
        $this->assign('department_list1', $department_list1);
        $this->assign('department_list2', $department_list2);
        $this->assign('department_list3', $department_list3);
        $this->assign('file_list', $file_list);
        $this->assign('customer_type_list', $customer_type_list);
        $this->assign('num_list', count($data));


        $rw22 = Cache::store('redis')->get("department_user" . $user['department_id']);

        $id_list = '';
        foreach ($rw22 as $rw) {
            $id_list = $id_list . $rw['id'] . ',';
        }
        $id_list = substr($id_list, 0, strlen($id_list) - 1);
        $user300_list = Dpid', $get['department_id1'])->select();
            } else {
                $department_list2 = [];
            }

            if (isset($get['department_id2']) && $get['department_id2'] !== "") {
                $department_list3 = Db::name('Department')->whereNull('is_deleted')->where('pid', $get['department_id2'])->select();
            } else {
                $department_list3 = [];
            }


        } else {

            // $department_list1=Db::name('Department')->whereNull('is_deleted')->where('pid',$user['department_id'])->select();

            if ($user['authorize'] == 17) {
                $department_list1 = Db::name('Department')->whereNull('is_deleted')->whereIn('id', $user['manage_company'])->select();
            } else {
                $department_list1 = Db::name('Department')->whereNull('is_deleted')->where('pid', $user['department_id'])->select();
            }

            if (isset($get['department_id1']) && $get['department_id1'] !== "") {
                $department_list2 = Dile_list = Cache::store('redis')->get('file_list');
        $customer_type_list = Cache::store('redis')->get('customer_type_list');
        $get = $this->request->get();
        if ($user['department_id'] == 1) {
            $department_list1 = Db::name('Department')->whereNull('is_deleted')->where('pid', 1)->select();

            if (isset($get['department_id1']) && $get['department_id1'] !== "") {
                $department_list2 = Db::name('Department')->whereNull('is_deleted')->where('pid', $get['department_id1'])->select();
            } else {
                $department_list2 = [];
            }

            if (isset($get['department_id2']) && $get['department_id2'] !== "") {
                $department_list3 = Db::name('Department')->whereNull('is_deleted')->where('pid', $get['department_id2'])->select();
            } else {
                $department_list3 = [];
            }


        } else {

            //$department_list1=Db::name('Department')->whereNull('is_deleted')->where('pid',$user['department_id'])->select();
            if ($user['authorize'] == 17) {
                $department_list1 = Db::name('Department')->whereNull('is_deleted')->whereIn('id', $user['manage_company'])->select();
            } else {
                $department_list1 = Db::name('Department')->whereNull('is_deleted')->where('pid', $user['department_id'])->select();
            }


            if (isset($get['department_id1']) && $get['department_id1'] !== "") {
                $department_list2 = Db::name('Department')->whereNull('is_deleted')->where('pid', $get['department_id1'])->select();
            } else {
                $department_list2 = [];
            }

            if (isset($get['department_id2']) && $get['department_id2'] !== "") {
                $department_list3 = Db::name('Department')->whereNull('is_deleted')->where('pid', $get['department_id2'])->select();
            } else {
                $department_list3 = [];
            }


        }


        //$department_list=ToolsService::arr2table($department_list);

        if (isset($get['department_id1']) && $get['department_id1'] !== "") {
            $department_id = $get['department_id1'];

            if (isset($get['department_id2']) && $get['department_id2'] != '') {
                $department_id = $get['department_id2'];

                if (isset($get['department_id3']) && $get['department_id3'] != '') {
                    $department_id = $get['department_id3'];


                }
            }

            $user_list = Cache::store('redis')->get('department_user' . $department_id);
        } else {
            $user_list = Cache::store('redis')->get('operation_user' . $user['id']);
        }


        $this->assign('department_id', $user['department_id']);
        $this->assign('department_list', $department_list);
        $this->assign('department_list1', $department_list1);
        $this->assign('department_list2', $department_list2);
        $this->assign('department_list3', $department_list3);
        $this->assign('user_list', $user_list);
        $this->assign('file_list', $file_list);
        $this->assign('customer_type_list', $customer_type_list);
        $this->assign('num_list', count($data));
    }

    /**
     * 列表数据处理
     * @param array $data
     */
    protected function _index_data_filter(&$data)
    {
        $user = session('user');
        foreach ($data as &$vo) {
            if ($user['department_id'] != 1) {
                if ($vo['money_demand'] < 50000) {
                    $vo['money_demand'] = '1万-5万';
                } else if ($vo['money_demand'] <= 100000 && $vo['money_demand'] >= 50000) {
                    $vo['money_demand'] = '5万-10万';
                }
            }
        }
        $department_list = Cache::store('redis')->get('operation_departemnt' . $user['id']);
        $department_list = ToolsService::arr2table($department_list);
        $user_list = Cache::store('redis')->get('operation_user' . $user['id']);
        $file_list = Cache::store('redis')->get('file_list');
        $customer_type_list = Cache::store('redis')->get('customer_type_list');
        $this->assign('department_list', $department_list);
        $this->assign('user_list', $user_list);
        $this->assign('file_list', $file_list);
        $this->assign('customer_type_list', $customer_type_list);
        $this->assign('num_list', count($data));
    }

    /**
     * 列表数据处理
     * @param array $data
     */
    function hidtel($phone)
    {
        $IsWhat = preg_match('/(0[0-9]{2,3}[\-]?[2-9][0-9]{6,7}[\-]?[0-9]?)/i', $phone); //固定电话
        if ($IsWhat == 1) {
            return preg_replace('/(0[0-9]{2,3}[\-]?[2-9])[0-9]{3,4}([0-9]{3}[\-]?[0-9]?)/i', '$1****$2', $phone);

        } else {
            return preg_replace('/(1[35687]{1}[0-9])[0-9]{4}([0-9]{4})/i', '$1****$2', $phone);
        }
    }

    protected function _index1_data_filter(&$data)
    {
        $get = $this->request->get();
        $fileids = [517];
        $user = session('user');
        foreach ($data as &$vo) {
            $xing = substr($vo['mobile'], 3, 4);  //获取手机号中间四位
            $vo['mobile'] = str_replace($xing, '****', $vo['mobile']);

            $lenth = mb_strlen($vo['name'], 'utf-8');
            if ($lenth >= 2) {
                $vo['name'] = substrCut($vo['name']);
            }
            if ($user['department_id'] != 1) {
                if (in_array($vo['file_id'], $fileids)) {
                    if ($vo['money_demand'] < 50000) {
                        $vo['money_demand'] = '5万-10万';
                    } else if ($vo['money_demand'] <= 100000 && $vo['money_demand'] >= 50000) {
                        $vo['money_demand'] = '5万-10万';
                    }
                } else {
                    if ($vo['money_demand'] < 50000) {
                        $vo['money_demand'] = '1万-5万';
                    } else if ($vo['money_demand'] <= 100000 && $vo['money_demand'] >= 50000) {
                        $vo['money_demand'] = '5万-10万';
                    }
                }

            }
            if ($vo['reset_time'] == null || $user['department_id'] == 1) {
                $vo['remarks'] = str_replace("\r\n", "<br>", $vo['remarks']);
            } else {
                $vo['remarks'] = '';
                $remark_list = Db::name("CrmCustomerRemark")->where('create_time', '>', $vo['reset_time'])->where('customer_id', $vo['id'])->order('create_time', 'desc')->select();
                foreach ($remark_list as $remark_obj) {
                    $vo['remarks'] = $vo['remarks'] . $remark_obj['remark'] . "<br>";;
                }

            }

        }

        if ($user['department_id'] == 1) {
            $department_list1 = Db::name('Department')->whereNull('is_deleted')->where('pid', 1)->select();

            if (isset($get['department_id1']) && $get['department_id1'] !== "") {
                $department_list2 = Db::name('Department')->whereNull('is_deleted')->where('pid', $get['department_id1'])->select();
            } else {
                $department_list2 = [];
            }

            if (isset($get['department_id2']) && $get['department_id2'] !== "") {
                $department_list3 = Db::name('Department')->whereNull('is_deleted')->where('pid', $get['department_id2'])->select();
            } else {
                $department_list3 = [];
            }


        } else {

            // $department_list1=Db::name('Department')->whereNull('is_deleted')->where('pid',$user['department_id'])->select();
            if ($user['authorize'] == 17) {
                $department_list1 = Db::name('Department')->whereNull('is_deleted')->whereIn('id', $user['manage_company'])->select();
            } else {
                $department_list1 = Db::name('Department')->whereNull('is_deleted')->where('pid', $user['department_id'])->select();
            }
            if (isset($get['department_id1']) && $get['department_id1'] !== "") {
                $department_list2 = Db::name('Department')->whereNull('is_deleted')->where('pid', $get['department_id1'])->select();
            } else {
                $department_list2 = [];
            }

            if (isset($get['department_id2']) && $get['department_id2'] !== "") {
                $department_list3 = Db::name('Department')->whereNull('is_deleted')->where('pid', $get['department_id2'])->select();
            } else {
                $department_list3 = [];
            }


        }


        //$department_list=ToolsService::arr2table($department_list);

        if (isset($get['department_id1']) && $get['department_id1'] !== "") {
            $department_id = $get['department_id1'];

            if (isset($get['department_id2']) && $get['department_id2'] != '') {
                $department_id = $get['department_id2'];

                if (isset($get['department_id3']) && $get['department_id3'] != '') {
                    $department_id = $get['department_id3'];


                }
            }

            $user_list = Cache::store('redis')->get('department_user' . $department_id);
        } else {
            $user['department_id']);

        $id_list = '';
        foreach ($user_list300 as $vo1) {
            $id_list = $id_list . $vo1['id'] . ',';
        }
        $id_list = substr($id_list, 0, strlen($id_list) - 1);
	Log::record(json_encode($id_list));
        // var_dump($id_list);exit;
        $company = Db::name("Department")->find($user['company_id']);
        $user300_list = [];
        if ($user['department_id'] != 1) {
            if ($company['is_limit'] == 1) {
                $user300_list = Db::query("SELECT id,name,nums from (select  t2.*,SUM(IF(num is null  , 0, num)) as nums     from (SELECT *  from  system_user  LEFT JOIN (SELECT user_id,count(*) as num      from  crm_customer   GROUP BY user_id) t1 on system_user.id=t1.user_id   where system_user.id in ($id_list)  GROUP BY id ) t2 where t2.num<300   GROUP BY id )t3 where t3.nums<300 and t3.is_deleted!=1");
            } else {
                //$user300_list = Db::query("SELECT id,name,nums from (select  t2.*,0 as nums     from (SELECT *  from  syer_list = Cache::store('redis')->get('operation_user' . $user['id']);
        }
        $department_list = Cache::store('redis')->get('operation_departemnt' . $user['id']);

        $file_list = Cache::store('redis')->get('file_list');
        $customer_type_list = Cache::store('redis')->get('customer_type_list');

        $city_list = Cache::store('redis')->get('city_list');
        $this->assign('city_list', $city_list);
        $this->assign('num_list', count($data));
        $this->assign('department_id', $user['department_id']);
        $this->assign('department_list', $department_list);
        $this->assign('department_list1', $department_list1);
        $this->assign('department_list2', $department_list2);
        $this->assign('department_list3', $department_list3);

        $this->assign('user_list', $user_list);
        $this->assign('file_list', $file_list);
        $this->assign('customer_type_list', $customer_type_list);

        $user_list300 = Cache::store('redis')->get("department_user" . $ush = mb_strlen($vo['name'], 'utf-8');
            if ($lenth >= 2) {
                $vo['name'] = substrCut($vo['name']);
            }
            if ($user['department_id'] != 1) {
                if ($vo['money_demand'] < 50000) {
                    $vo['money_demand'] = '1万-5万';
                } else if ($vo['money_demand'] <= 100000 && $vo['money_demand'] >= 50000) {
                    $vo['money_demand'] = '5万-10万';
                }
            }
            if ($vo['reset_time'] == null || $user['department_id'] == 1) {
                $vo['remarks'] = str_replace("\r\n", "<br>", $vo['remarks']);
            } else {
                $vo['remarks'] = '';
                $remark_list = Db::name("CrmCustomerRemark")->where('create_time', '>', $vo['reset_time'])->where('customer_id', $vo['id'])->order('create_time', 'desc')->select();
                foreach ($remark_list as $remark_obj) {
                    $vo['remarks'] = $vo['remarks'] . $remark_obj['remark'] . "<br>";;
                }
 stem_user  LEFT JOIN (SELECT user_id,count(*) as num      from  crm_customer   GROUP BY user_id) t1 on system_user.id=t1.user_id   where system_user.id in ($id_list)  GROUP BY id ) t2 where t2.num<300   GROUP BY id )t3 where t3.nums<300 and t3.is_deleted!=1");

                $user300_list = Db::query("SELECT su.id, su.name, 0 AS nums FROM system_user su LEFT JOIN (SELECT user_id, COUNT(*) AS num FROM crm_customer GROUP BY user_id) t1 ON su.id = t1.user_id WHERE su.id IN ($id_list) AND su.is_deleted != 1 AND COALESCE(t1.num, 0) < 300");

            }
        }
        //$user300_list=Cache::store('redis')->get('department_user'.$user['department_id']);

        $this->assign('user300_list', $user300_list);

    }

    protected function _public_pool_data_filter(&$data)
    {
        $user = session('user');

        foreach ($data as &$vo) {
            $xing = substr($vo['mobile'], 3, 4);  //获取手机号中间四位
            $vo['mobile'] = str_replace($xing, '****', $vo['mobile']);
            $lent           }
        }
        $department_list = Cache::store('redis')->get('operation_departemnt' . $user['id']);
        $department_list = ToolsService::arr2table($department_list);
        $get = $this->request->get();
        if ($user['department_id'] == 1) {
            $department_list1 = Db::name('Department')->whereNull('is_deleted')->where('pid', 1)->select();

            if (isset($get['department_id1']) && $get['department_id1'] !== "") {
                $department_list2 = Db::name('Department')->whereNull('is_deleted')->where('pid', $get['department_id1'])->select();
            } else {
                $department_list2 = [];
            }
            if (isset($get['department_id2']) && $get['department_id2'] !== "") {
                $department_list3 = Db::name('Department')->whereNull('is_deleted')->where('pid', $get['department_id2'])->select();
            } else {
                $department_list3 = [];
            }
        } else {

            //if($user['authorize'] == 17){
            //    $department_list1=Db::name('Department')->whereNull('is_deleted')->whereIn('id',$user['manage_company'])->select();
            //}else{
            $department_list1 = Db::name('Department')->whereNull('is_deleted')->where('pid', $user['department_id'])->select();
            //}

            if (isset($get['department_id1']) && $get['department_id1'] !== "") {
                $department_list2 = Db::name('Department')->whereNull('is_deleted')->where('pid', $get['department_id1'])->select();
            } else {
                $department_list2 = [];
            }
            if (isset($get['department_id2']) && $get['department_id2'] !== "") {
                $department_list3 = Db::name('Department')->whereNull('is_deleted')->where('pid', $get['department_id2'])->select();
            } else {
                $department_list3 = [];
            }
        }


        //$department_list=ToolsService::arr2table($department_list);
        if (isset($get['department_id1']) && $get['department_id1'] !== "") {
            $department_id = $get['department_id1'];

            if (isset($get['department_id2']) && $get['department_id2'] != '') {
                $department_id = $get['department_id2'];
                if (isset($get['department_id3']) && $get['department_id3'] != '') {
                    $department_id = $get['department_id3'];
                }
            }
            $user_list = Cache::store('redis')->get('department_user' . $department_id);
        } else {
            $user_list = Cache::store('redis')->get('operation_user' . $user['id']);
        }
        $department_list = Cache::store('redis')->get('operation_departemnt' . $user['id']);
        $file_list = Cache::store('redis')->get('file_list');
        $customer_type_list = Cache::store('redis')->get('customer_type_list');
        $this->assign('num_list', count($data));
        $this->assign('department_id', $user['department_id']);
        $this->assign('department_list', $department_list);
        $this->assign('department_list1', $department_list1);
        $this->assign('department_list2', $department_list2);
        $this->assign('department_list3', $department_list3);
        $this->assign('user_list', $user_list);
        $this->assign('file_list', $file_list);
        $this->assign('customer_type_list', $customer_type_list);

        //$rw22=Cache::store('redis')->get("department_user".$user['department_id']);


        $user_list300 = Cache::store('redis')->get("department_user" . $user['department_id']);

        $id_list = '';
        foreach ($user_list300 as $vo1) {
            $id_list = $id_list . $vo1['id'] . ',';
        }
        $id_list = substr($id_list, 0, strlen($id_list) - 1);

        $company = Db::name("Department")->find($user['company_id']);
        if ($company['is_limit'] == 1) {
            $user300_list = Db::query("SELECT id,name,nums ,t3.is_public from (select  t2.*,SUM(IF(num is null  , 0, num)) as nums,user_allot.is_public      from (SELECT *  from  system_user  LEFT JOIN (SELECT user_id,count(*) as num      from  crm_customer   GROUP BY user_id) t1 on system_user.id=t1.user_id   where system_user.id in ($id_list)  GROUP BY id ) t2  Left Join user_allot on t2.id=user_allot.user_id  GROUP BY id )t3 where t3.nums<300 and t3.is_deleted!=1");
        } else {
            $user300_list = Db::query("SELECT id,name,nums ,t3.is_public from (select  t2.*,0 as nums ,user_allot.is_public      from (SELECT *  from  system_user  LEFT JOIN (SELECT user_id,count(*) as num     from  crm_customer   GROUP BY user_id) t1 on system_user.id=t1.user_id   where system_user.id in ($id_list)  GROUP BY id ) t2  Left Join user_allot on t2.id=user_allot.user_id  GROUP BY id )t3 where t3.nums<300 and t3.is_deleted!=1");

        }
        //$user300_list=Cache::store('redis')->get('department_user'.$user['department_id']);

        $this->assign('user300_list', $user300_list);
    }

    /**
     * 列表数据处理
     * @param array $data
     */
    protected function _index2_data_filter(&$data)
    {
        $user = session('user');
        foreach ($data as &$vo) {
            $xing = substr($vo['mobile'], 3, 4);  //获取手机号中间四位
            $vo['mobile'] = str_replace($xing, '****', $vo['mobile']);
            $lenth = mb_strlen($vo['name'], 'utf-8');
            if ($lenth >= 2) {
                $vo['name'] = substrCut($vo['name']);
            }

            if ($user['department_id'] != 1) {
                if ($vo['money_demand'] < 50000) {
                    $vo['money_demand'] = '1万-5万';
                } else if ($vo['money_demand'] <= 100000 && $vo['money_demand'] >= 50000) {
                    $vo['money_demand'] = '5万-10万';
                }
            }

            if ($vo['reset_time'] == null || $user['department_id'] == 1) {
                $vo['remarks'] = str_replace("\r\n", "<br>", $vo['remarks']);
            } else {
                $vo['remarks'] = '';
                $remark_list = Db::name("CrmCustomerRemark")->where('create_time', '>', $vo['reset_time'])->where('customer_id', $vo['id'])->order('create_time', 'desc')->select();
                foreach ($remark_list as $remark_obj) {
                    $vo['remarks'] = $vo['remarks'] . $remark_obj['remark'] . "<br>";;
                }

            }
        }

        $department_list = Cache::store('redis')->get('operation_departemnt' . $user['id']);
        $department_list = ToolsService::arr2table($department_list);
        $user_list = Cache::store('redis')->get('operation_user' . $user['id']);
        $file_list = Cache::store('redis')->get('file_list');
        $customer_type_list = Cache::store('redis')->get('customer_type_list');

        $files = Db::name("CrmCustomer")->field('id,file_id')->where('user_id', $user['id'])->group('file_id')->select();

        $file_ids = [];
        foreach ($files as $k => $v) {
            $file_ids[] = $v['file_id'];
        }
        $new_file = [];
        foreach ($file_list as $kk => $vv) {
            if (in_array($vv['id'], $file_ids)) {
                $new_file[$kk] = $vv;
            }
        }
        $file_list = $new_file;

        $this->assign('department_list', $department_list);
        $this->assign('user_list', $user_list);
        $this->assign('file_list', $file_list);
        $this->assign('customer_type_list', $customer_type_list);
        $this->assign('num_list', count($data));
    }

    protected function _index10_data_filter(&$data)
    {
        $user = session('user');
        foreach ($data as &$vo) {
            if ($user['department_id'] != 1) {
                if ($vo['money_demand'] < 50000) {
                    $vo['money_demand'] = '1万-5万';
                } else if ($vo['money_demand'] <= 100000 && $vo['money_demand'] >= 50000) {
                    $vo['money_demand'] = '5万-10万';
                }
            }

            if ($vo['reset_time'] == null || $user['department_id'] == 1) {
                $vo['remarks'] = str_replace("\r\n", "<br>", $vo['remarks']);
            } else {
                $vo['remarks'] = '';
                $remark_list = Db::name("CrmCustomerRemark")->where('create_time', '>', $vo['reset_time'])->where('customer_id', $vo['id'])->order('create_time', 'desc')->select();
                foreach ($remark_list as $remark_obj) {
                    $vo['remarks'] = $vo['remarks'] . $remark_obj['remark'] . "<br>";;
                }

            }
        }

        $department_list = Cache::store('redis')->get('operation_departemnt' . $user['id']);
        $department_list = ToolsService::arr2table($department_list);
        $user_list = Cache::store('redis')->get('operation_user' . $user['id']);
        $file_list = Cache::store('redis')->get('file_list');
        $customer_type_list = Cache::store('redis')->get('customer_type_list');

        $files = Db::name("CrmCustomer")->field('id,file_id')->where('user_id', $user['id'])->group('file_id')->select();

        $file_ids = [];
        foreach ($files as $k => $v) {
            $file_ids[] = $v['file_id'];
        }
        $new_file = [];
        foreach ($file_list as $kk => $vv) {
            if (in_array($vv['id'], $file_ids)) {
                $new_file[$kk] = $vv;
            }
        }
        $file_list = $new_file;

        $this->assign('department_list', $department_list);
        $this->assign('user_list', $user_list);
        $this->assign('file_list', $file_list);
        $this->assign('customer_type_list', $customer_type_list);
        $this->assign('num_list', count($data));
    }

    /**
     * 列表数据处理
     * @param array $data
     */
    protected function _index3_data_filter(&$data)
    {
        $user = session('user');
        foreach ($data as &$vo) {
            $xing = substr($vo['mobile'], 3, 4);  //获取手机号中间四位
            $vo['mobile'] = str_replace($xing, '****', $vo['mobile']);
            $lenth = mb_strlen($vo['name'], 'utf-8');
            if ($lenth >= 2) {
                $vo['name'] = substrCut($vo['name']);
            }
            if ($user['department_id'] != 1) {
                if ($vo['money_demand'] < 50000) {
                    $vo['money_demand'] = '1万-5万';
                } else if ($vo['money_demand'] <= 100000 && $vo['money_demand'] >= 50000) {
                    $vo['money_demand'] = '5万-10万';
                }
            }
            if ($vo['reset_time'] == null || $user['department_id'] == 1) {
                $vo['remarks'] = str_replace("\r\n", "<br>", $vo['remarks']);
            } else {
                $vo['remarks'] = '';
                $remark_list = Db::name("CrmCustomerRemark")->where('create_time', '>', $vo['reset_time'])->where('customer_id', $vo['id'])->order('create_time', 'desc')->select();
                foreach ($remark_list as $remark_obj) {
                    $vo['remarks'] = $vo['remarks'] . $remark_obj['remark'] . "<br>";;
                }
            }
        }

        $department_list = Cache::store('redis')->get('operation_departemnt' . $user['id']);
        $department_list = ToolsService::arr2table($department_list);
        $user_list = Cache::store('redis')->get('operation_user' . $user['id']);
        $file_list = Cache::store('redis')->get('file_list');
        $customer_type_list = Cache::store('redis')->get('customer_type_list');

        $this->assign('department_list', $department_list);
        $this->assign('user_list', $user_list);
        $this->assign('file_list', $file_list);
        $this->assign('customer_type_list', $customer_type_list);
        $this->assign('num_list', count($data));
    }

    /**
     * 列表数据处理
     * @param array $data
     */
    protected function _index4_data_filter(&$data)
    {
        $user = session('user');
        foreach ($data as &$vo) {
            if ($user['department_id'] != 1) {
                if ($vo['money_demand'] < 50000) {
                    $vo['money_demand'] = '1万-5万';
                } else if ($vo['money_demand'] <= 100000 && $vo['money_demand'] >= 50000) {
                    $vo['money_demand'] = '5万-10万';
                }
            }
        }
        $department_list = Cache::store('redis')->get('operation_departemnt' . $user['id']);
        $department_list = ToolsService::arr2table($department_list);

        $file_list = Cache::store('redis')->get('file_list');
        $customer_type_list = Cache::store('redis')->get('customer_type_list');
        $get = $this->request->get();
        if ($user['department_id'] == 1) {
            $department_list1 = Db::name('Department')->whereNull('is_deleted')->where('pid', 1)->select();

            if (isset($get['department_id1']) && $get['department_id1'] !== "") {
                $department_list2 = Db::name('Department')->whereNull('is_deleted')->where('pid', $get['department_id1'])->select();
            } else {
                $department_list2 = [];
            }

            if (isset($get['department_id2']) && $get['department_id2'] !== "") {
                $department_list3 = Db::name('Department')->whereNull('is_deleted')->where('pid', $get['department_id2'])->select();
            } else {
                $department_list3 = [];
            }


        } else {


            if ($user['authorize'] == 17) {
                $department_list1 = Db::name('Department')->whereNull('is_deleted')->whereIn('id', $user['manage_company'])->select();
            } else {
                $department_list1 = Db::name('Department')->whereNull('is_deleted')->where('pid', $user['department_id'])->select();
            }


            if (isset($get['department_id1']) && $get['department_id1'] !== "") {
                $department_list2 = Db::name('Department')->whereNull('is_deleted')->where('pid', $get['department_id1'])->select();
            } else {
                $department_list2 = [];
            }

            if (isset($get['department_id2']) && $get['department_id2'] !== "") {
                $department_list3 = Db::name('Department')->whereNull('is_deleted')->where('pid', $get['department_id2'])->select();
            } else {
                $department_list3 = [];
            }


        }


        //$department_list=ToolsService::arr2table($department_list);

        if (isset($get['department_id1']) && $get['department_id1'] !== "") {
            $department_id = $get['department_id1'];

            if (isset($get['department_id2']) && $get['department_id2'] != '') {
                $department_id = $get['department_id2'];

                if (isset($get['department_id3']) && $get['department_id3'] != '') {
                    $department_id = $get['department_id3'];


                }
            }

            $user_list = Cache::store('redis')->get('department_user' . $department_id);
        } else {
            $user_list = Cache::store('redis')->get('operation_user' . $user['id']);
        }


        $this->assign('department_id', $user['department_id']);
        $this->assign('department_list', $department_list);
        $this->assign('department_list1', $department_list1);
        $this->assign('department_list2', $department_list2);
        $this->assign('department_list3', $department_list3);
        $this->assign('user_list', $user_list);
        $this->assign('file_list', $file_list);
        $this->assign('customer_type_list', $customer_type_list);
        $this->assign('num_list', count($data));
    }

    protected function _index7_data_filter(&$data)
    {
        $user = session('user');
        foreach ($data as &$vo) {
            //$xing = substr($vo['mobile'],3,4);  //获取手机号中间四位
            //$vo['mobile'] =    str_replace($xing,'****',$vo['mobile']);
            if ($user['department_id'] != 1) {
                if ($vo['money_demand'] < 50000) {
                    $vo['money_demand'] = '1万-5万';
                } else if ($vo['money_demand'] <= 100000 && $vo['money_demand'] >= 50000) {
                    $vo['money_demand'] = '5万-10万';
                }
            }
            if ($vo['reset_time'] == null || $user['department_id'] == 1) {
                $vo['remarks'] = str_replace("\r\n", "<br>", $vo['remarks']);
            } else {
                $vo['remarks'] = '';
                $remark_list = Db::name("CrmCustomerRemark")->where('create_time', '>', $vo['reset_time'])->where('customer_id', $vo['id'])->order('create_time', 'desc')->select();
                foreach ($remark_list as $remark_obj) {
                    $vo['remarks'] = $vo['remarks'] . $remark_obj['remark'] . "<br>";;
                }

            }
            if (isset($vo['user_id'])) {
                $uname = Db::name("SystemUser")->where('id', $vo['user_id'])->value('name');
                $vo['uname'] = $uname;
            } else {
                $vo['uname'] = '无';
            }
        }

        $department_list = Cache::store('redis')->get('department_list');
        $user_list = Cache::store('redis')->get('user_list');
        $file_list = Cache::store('redis')->get('file_list');
        $customer_type_list = Cache::store('redis')->get('customer_type_list');

        $this->assign('department_list', $department_list);
        $this->assign('user_list', $user_list);
        $this->assign('file_list', $file_list);
        $this->assign('customer_type_list', $customer_type_list);
        $this->assign('num_list', count($data));
    }

    /**
     * /**
     * 列表数据处理
     * @param array $data
     */
    protected function _form_filter()
    {
        if (!$this->request->isPost()) {

            $customer_type_list = Cache::store('redis')->get('customer_type_list');
            $this->assign(['customer_type_list' => $customer_type_list]);
        }
    }


    /**
     * 权限添加
     */
    public function add()
    {
        if (!$this->request->isPost()) {
            $file_list = Cache::store('redis')->get('file_list');
            $this->assign('file_list', $file_list);
            return $this->_form($this->table, 'form');
        } else {

            $data = $this->request->param();

            $customer_model['name'] = $data['name'];
            $customer_model['mobile'] = $data['mobile'];
            $customer_model['phone'] = $data['phone'];
            $customer_model['city'] = $data['city'];
            $customer_model['file_id'] = $data['file_id'];

            $customer_model['age'] = $data['age'];
            $customer_model['place'] = $data['place'];
            $customer_model['is_marry'] = $data['is_marry'];
            $customer_model['is_know'] = $data['is_know'];
            $customer_model['money_demand'] = $data['money_demand'];
            $customer_model['credit_score'] = $data['credit_score'];
            $customer_model['webank'] = $data['webank'];
            $customer_model['sex'] = $data['sex'];
            $customer_model['customer_type_id'] = $data['customer_type_id'];
            $customer_model['create_time'] = date('y-m-d H:i:s', time());
            $customer_model['status'] = $data['status'];
            $customer_model['is_house'] = $data['is_house'];
            $customer_model['is_car'] = $data['is_car'];
            $customer_model['is_credit'] = $data['is_credit'];
            $customer_model['is_insurance'] = $data['is_insurance'];
            $customer_model['is_work'] = $data['is_work'];
            $customer_model['is_fund'] = $data['is_fund'];
            //注释上线需要把1删除

            $check = Db::name('CrmCustomer')->where('mobile', $data['mobile'])->find();
            if ($check) {
                $this->error(' 数据已存在！', '');
            }


            if (isset($data['is_company'])) {
                $customer_model['is_company'] = 1;
            } else {
                $customer_model['is_company'] = 0;
            }
            if (isset($data['is_social'])) {
                $customer_model['is_social'] = 1;
            } else {
                $customer_model['is_social'] = 0;
            }


            $user = session('user');
            if ($data['remarks'] == "") {

            } else {
                $customer_model['remarks'] = $data['remarks'] . date('Y-m-d H:i:s', time()) . $user['name'];
                $customer_model['remark_time'] = date('Y-m-d H:i:s', time());
            }

            $customer_model['user_id'] = $user['id'];
            $customer_model['department_id'] = $user['department_id'];
            $customer_id = Db::name('CrmCustomer')->insertGetId($customer_model);
            $crm_customer_remark['customer_id'] = $customer_id;
            $crm_customer_remark['remark'] = $data['remarks'] . date('Y-m-d H:i:s', time()) . $user['name'];
            Db::name('CrmCustomerRemark')->insert($crm_customer_remark);
            if (isset($data['is_house']) && $data['is_house'] !== '0') {
                $house_model['house_type'] = $data['house_type'];
                $house_model['area'] = $data['area'];
                $house_model['house_value'] = $data['house_value'];
                $house_model['house_times'] = $data['house_times'];
                $house_model['house_month_pay'] = $data['house_month_pay'];
                $house_model['house_bank_name'] = $data['house_bank_name'];
                $house_model['create_time'] = date('y-m-d H:i:s', time());
                $house_model['customer_id'] = $customer_id;
                Db::name('CrmHouse')->insert($house_model);
            }
            if (isset($data['is_car']) && $data['is_car'] !== '0') {
                $car_model['car_type'] = $data['car_type'];
                $car_model['time_limit'] = $data['time_limit'];
                $car_model['car_bank_name'] = $data['car_bank_name'];
                $car_model['car_month_pay'] = $data['car_month_pay'];
                $car_model['car_times'] = $data['car_times'];
                $car_model['car_value'] = $data['car_value'];
                $car_model['create_time'] = date('y-m-d H:i:s', time());
                $car_model['customer_id'] = $customer_id;
                Db::name('CrmCar')->insert($car_model);
            }
            if (isset($data['is_credit']) && $data['is_credit'] !== '0') {
                $credit_model['card_amount'] = $data['card_amount'];
                $credit_model['is_coverdue'] = $data['is_coverdue'];
                $credit_model['loan_amount'] = $data['loan_amount'];
                $credit_model['is_loverdue'] = $data['is_loverdue'];
                $credit_model['create_time'] = date('y-m-d H:i:s', time());
                $credit_model['customer_id'] = $customer_id;
                Db::name('CrmCredit')->insert($credit_model);
            }
            if (isset($data['is_company']) && $data['is_company'] !== '0') {
                $company_model['licence_years'] = $data['licence_years'];
                $company_model['is_legal'] = $data['is_legal'];
                $company_model['create_time'] = date('y-m-d H:i:s', time());
                $company_model['customer_id'] = $customer_id;
                Db::name('CrmCompany')->insert($company_model);
            }
            if (isset($data['is_insurance']) && $data['is_insurance'] !== '0') {
                $insurance_model['insurance_pay_type'] = $data['insurance_pay_type'];
                $insurance_model['remark'] = $data['remark'];
                $insurance_model['company'] = $data['company'];
                $insurance_model['create_time'] = date('y-m-d H:i:s', time());
                $insurance_model['customer_id'] = $customer_id;
                Db::name('CrmInsurance')->insert($insurance_model);
            }
            if (isset($data['is_social']) && $data['is_social'] !== '0') {
                $social_model['social_years'] = $data['social_years'];
                $social_model['social_money'] = $data['social_money'];
                $social_model['create_time'] = date('y-m-d H:i:s', time());
                $social_model['customer_id'] = $customer_id;
                Db::name('CrmSocial')->insert($social_model);
            }
            if (isset($data['is_fund']) && $data['is_fund'] !== '0') {
                $fund_model['fund_years'] = $data['fund_years'];
                $fund_model['fund_money'] = $data['fund_money'];
                $fund_model['create_time'] = date('y-m-d H:i:s', time());
                $fund_model['customer_id'] = $customer_id;
                Db::name('CrmFund')->insert($fund_model);
            }
            if (isset($data['is_work']) && $data['is_work'] !== '0') {
                $work_model['company_nature'] = $data['company_nature'];
                $work_model['money'] = $data['money'];
                $work_model['pay_type'] = $data['pay_type'];
                $work_model['create_time'] = date('y-m-d H:i:s', time());
                $work_model['customer_id'] = $customer_id;
                Db::name('CrmWork')->insert($work_model);
            }
            $this->success('恭喜, 数据保存成功!', '');
        }
    }

    /**
     * 导入
     */
    public function import()
    {
        if ($this->request->isPost()) {
            vendor("PHPExcel.PHPExcel");
            if ($_FILES['file']['name']) {
                $file = request()->file('file');

                $data = $this->request->param();
                //选择了分公司
                if (isset($data['department_id']) && $data['department_id'] != "") {
                    $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                    if ($info) {
                        // 成功上传后 获取上传信息
                        // 输出 jpg

                        $inputFileName = ROOT_PATH . 'public/uploads/' . $thismonth = date('Ymd') . '/' . $info->getFilename();
                        $file_model['file_name'] = $data['file_name'];

                        $file_model['file_path'] = $inputFileName;
                        if ($data['file_name'] == "") {
                            $file_model_id = $data['file_id'];
                        } else {
                            $file_model_id = Db::name('CrmFile')->insertGetId($file_model);
                            $list = Db::name("CrmFile")->select();
                            Cache::store('redis')->set('file_list', $list);
                            foreach ($list as $file) {
                                Cache::store('redis')->set('file_id_' . $file['id'], $file);
                            }
                        }
                        $batch_model['batch_name'] = $data['batch_name'];

                        $batch_model['batch_path'] = $inputFileName;
                        $batch_model_id = Db::name('CrmBatch')->insertGetId($batch_model);
                        if ($info->getExtension() == "xlsx") {
                            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
                        } else {
                            $objReader = PHPExcel_IOFactory::createReader('Excel5');
                        }
                        $objPHPExcel = $objReader->load($inputFileName);
                        $sheetDatas = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
                        unset($sheetDatas[1]);
			            $repeat = [];

                        foreach ($sheetDatas as $value) {
                            if ($customer_model['mobile'] = $value['B'] == null) {

                            } else {
                                $customer_model['name'] = trim($value['A']);
                                $customer_model['mobile'] = trim($value['B']);
                                $customer_model['age'] = trim($value['C']);
                                $customer_model['remarks'] = trim($value['D']);
                                $customer_model['money_demand'] = trim($value['E']);
                                $customer_model['city'] = strpos($value['F'], '市') !== false ? trim($value['F']) : trim($value['F']) . '市';
                                $customer_model['file_id'] = $file_model_id;
                                $customer_model['batch_id'] = $batch_model_id;
                                $customer_model['department_id'] = $data['department_id'];
                                $customer_model['status'] = 1;
                                if (strpos($customer_model['remarks'], '房') !== false) {
                                    $customer_model['is_house'] = 1;
                                } else {
                                    $customer_model['is_house'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '车') !== false) {
                                    $customer_model['is_car'] = 1;
                                } else {
                                    $customer_model['is_car'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '社保') !== false) {
                                    $customer_model['is_social'] = 1;
                                } else {
                                    $customer_model['is_social'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '公积金') !== false) {
                                    $customer_model['is_fund'] = 1;
                                } else {
                                    $customer_model['is_fund'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '保单') !== false) {
                                    $customer_model['is_insurance'] = 1;
                                } else {
                                    $customer_model['is_insurance'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '微粒贷') !== false) {
                                    $customer_model['webank'] = 1;
                                } else {
                                    $customer_model['webank'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '工资') !== false) {
                                    $customer_model['is_work'] = 1;
                                } else {
                                    $customer_model['is_work'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '信用卡') !== false) {
                                    $customer_model['is_credit'] = 1;
                                } else {
                                    $customer_model['is_credit'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '企业税') !== false) {
                                    $customer_model['is_tax'] = 1;
                                } else {
                                    $customer_model['is_tax'] = 0;
                                }
                                $customer_model['mobile_md5'] = md5($customer_model['mobile']);

                                $check = Db::name('CrmCustomer')->where('mobile', $customer_model['mobile'])->find();
                                if ($check) {
				                    $repeat[] = [
                                        'mobile' => $customer_model['mobile']
                                    ];
                                    continue;
                                } else {
                                    Db::name('CrmCustomer')->insert($customer_model);
                                }
                            }
			}
			if (count($repeat) > 0){
                            $xlsName = '重复手机号'. date("Y/m/d i:m:s");
                            $xlsCell = array(
                                array('mobile', '手机号'),
                            );
                            $this->exportExcel($xlsName, $xlsCell, $repeat);
                        }
                        $this->success('恭喜, 数据保存成功!');
                    } else {
                        // 上传失败获取错误信息
                        echo $file->getError();
                    }
                } else {
                    $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                    if ($info) {
                        // 成功上传后 获取上传信息
                        // 输出 jpg

                        $inputFileName = ROOT_PATH . 'public/uploads/' . $thismonth = date('Ymd') . '/' . $info->getFilename();
                        $file_model['file_name'] = $data['file_name'];

                        $file_model['file_path'] = $inputFileName;
                        if ($data['file_name'] == "") {
                            $file_model_id = $data['file_id'];
                        } else {
                            $file_model_id = Db::name('CrmFile')->insertGetId($file_model);
                            $list = Db::name("CrmFile")->select();
                            Cache::store('redis')->set('file_list', $list);
                            foreach ($list as $file) {
                                Cache::store('redis')->set('file_id_' . $file['id'], $file);
                            }
                        }
                        $batch_model['batch_name'] = $data['batch_name'];

                        $batch_model['batch_path'] = $inputFileName;
                        $batch_model_id = Db::name('CrmBatch')->insertGetId($batch_model);
                        if ($info->getExtension() == "xlsx") {
                            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
                        } else {
                            $objReader = PHPExcel_IOFactory::createReader('Excel5');
                        }
                        $objPHPExcel = $objReader->load($inputFileName);

                        $sheetDatas = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
                        unset($sheetDatas[1]);

                        foreach ($sheetDatas as $value) {
                            if ($customer_model['mobile'] = $value['B'] == null) {

                            } else {


                                $customer_model['name'] = trim($value['A']);
                                $customer_model['mobile'] = trim($value['B']);
                                $customer_model['age'] = trim($value['C']);
                                $customer_model['remarks'] = trim($value['D']);
                                $customer_model['money_demand'] = trim($value['E']);
                                $customer_model['city'] = strpos($value['F'], '市') !== false ? trim($value['F']) : trim($value['F']) . '市';
                                $customer_model['file_id'] = $file_model_id;
                                $customer_model['batch_id'] = $batch_model_id;
                                //$customer_model['department_id'] = $data['department_id'];
                                $customer_model['status'] = 1;
                                if (strpos($customer_model['remarks'], '房') !== false) {
                                    $customer_model['is_house'] = 1;
                                } else {
                                    $customer_model['is_house'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '车') !== false) {
                                    $customer_model['is_car'] = 1;
                                } else {
                                    $customer_model['is_car'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '社保') !== false) {
                                    $customer_model['is_social'] = 1;
                                } else {
                                    $customer_model['is_social'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '公积金') !== false) {
                                    $customer_model['is_fund'] = 1;
                                } else {
                                    $customer_model['is_fund'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '保单') !== false) {
                                    $customer_model['is_insurance'] = 1;
                                } else {
                                    $customer_model['is_insurance'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '微粒贷') !== false) {
                                    $customer_model['webank'] = 1;
                                } else {
                                    $customer_model['webank'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '工资') !== false) {
                                    $customer_model['is_work'] = 1;
                                } else {
                                    $customer_model['is_work'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '信用卡') !== false) {
                                    $customer_model['is_credit'] = 1;
                                } else {
                                    $customer_model['is_credit'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '企业税') !== false) {
                                    $customer_model['is_tax'] = 1;
                                } else {
                                    $customer_model['is_tax'] = 0;
                                }

                                Db::name('CrmCustomerTemporary')->insert($customer_model);
                            }

                        }
                        $this->success('恭喜, 数据保存成功!');

                    } else {
                        // 上传失败获取错误信息
                        echo $file->getError();
                    }

                }

            } else {
                $this->error('未选择文件!', url('customer/temporary/index'));
            }
        } else {
            $file_list = Cache::store('redis')->get('file_list');
            $this->assign('file', $file_list);
            $department_list = DB::name("Department")->where("pid", 1)->select();
            $this->assign('department_list', $department_list);
            return $this->_form($this->table, 'import');
        }
    }

    public function imports()
    {
        set_time_limit(0);
	ini_set('memory_limit', '256M');

        if ($this->request->isPost()) {

	    $file = request()->file('file');
	    if (!$file) {
                return json(['code' => 0, 'msg' => '未上传文件']);
	    }
            $user = session('user');
            vendor("PHPExcel.PHPExcel");
            if ($_FILES['file']['name']) {

                $data = $this->request->param();
                //选择了分公司
                if ($user['department_id']) {
                    $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                    if ($info) {
                        // 成功上传后 获取上传信息
                        // 输出 jpg

                        $inputFileName = ROOT_PATH . 'public/uploads/' . $thismonth = date('Ymd') . '/' . $info->getFilename();


                        $file_model['file_path'] = $inputFileName;
                        $file_model_id = $data['file_id'];
                        $batch_model['batch_name'] = $data['batch_name'];

                        $batch_model['batch_path'] = $inputFileName;
                        $batch_model_id = Db::name('CrmBatch')->insertGetId($batch_model);
                        if ($info->getExtension() == "xlsx") {
                            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
                        } else {
                            $objReader = PHPExcel_IOFactory::createReader('Excel5');
                        }
                        $objPHPExcel = $objReader->load($inputFileName);
                        $sheetDatas = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
                        unset($sheetDatas[1]);
                        $arrmobile = [];
                        foreach ($sheetDatas as $value) {
                            $arrmobile[] = trim($value['B']);
                        }

                        $findM = Db::name('CrmCustomer')->whereIn('mobile', $arrmobile)->column('mobile');
                        foreach ($sheetDatas as $value) {
                            if ($customer_model['mobile'] = $value['B'] == null) {

                            } else {
                                $customer_model['name'] = trim($value['A']);
                                $customer_model['mobile'] = trim($value['B']);
                                $customer_model['age'] = trim($value['C']);
                                $customer_model['remarks'] = trim($value['D']);
                                $customer_model['money_demand'] = trim($value['E']);
                                $customer_model['city'] = strpos($value['F'], '市') !== false ? trim($value['F']) : trim($value['F']) . '市';
                                $customer_model['customer_type_id'] = intval($value['G']);
                                $customer_model['file_id'] = $file_model_id;
                                $customer_model['batch_id'] = $batch_model_id;
                                $customer_model['department_id'] = $user['department_id'];
                                $customer_model['status'] = 1;
                                $customer_model['is_public'] = 1;
                                if (in_array($customer_model['mobile'], $findM)) {
                                    continue;
                                }
                                if (strpos($customer_model['remarks'], '房') !== false) {
                                    $customer_model['is_house'] = 1;
                                } else {
                                    $customer_model['is_house'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '车') !== false) {
                                    $customer_model['is_car'] = 1;
                                } else {
                                    $customer_model['is_car'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '社保') !== false) {
                                    $customer_model['is_social'] = 1;
                                } else {
                                    $customer_model['is_social'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '公积金') !== false) {
                                    $customer_model['is_fund'] = 1;
                                } else {
                                    $customer_model['is_fund'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '保单') !== false) {
                                    $customer_model['is_insurance'] = 1;
                                } else {
                                    $customer_model['is_insurance'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '微粒贷') !== false) {
                                    $customer_model['webank'] = 1;
                                } else {
                                    $customer_model['webank'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '工资') !== false) {
                                    $customer_model['is_work'] = 1;
                                } else {
                                    $customer_model['is_work'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '信用卡') !== false) {
                                    $customer_model['is_credit'] = 1;
                                } else {
                                    $customer_model['is_credit'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '企业税') !== false) {
                                    $customer_model['is_tax'] = 1;
                                } else {
                                    $customer_model['is_tax'] = 0;
                                }
				$customer_model['mobile_md5'] = md5($customer_model['mobile']);
                                $check = Db::name('CrmCustomer')->where('mobile', $customer_model['mobile'])->find();
                                if ($check) {
                                    continue;
                                } else {
                                    Db::name('CrmCustomer')->insert($customer_model);
                                }


                            }
                        }
                        $this->success('恭喜, 数据保存成功!');
                    } else {
                        // 上传失败获取错误信息
                        echo $file->getError();
                    }
                } else {
                    $this->error('请选择部门!', url('customer/customer/public_pool'));
                }

            } else {
                $this->error('未选择文件!', url('customer/customer/public_pool'));
            }
        } else {
            $file_list = Cache::store('redis')->get('file_list');
            $this->assign('file', $file_list);
            $department_list = DB::name("Department")->where("pid", 1)->select();
            $this->assign('department_list', $department_list);
            return $this->_form($this->table, 'imports');
        }
    }

    public function importmy()
    {
        if ($this->request->isPost()) {
            set_time_limit(0);

            ini_set("memory_limit", "1024M");
            $user = session('user');
            vendor("PHPExcel.PHPExcel");
            if ($_FILES['file']['name']) {
                $file = request()->file('file');

                $data = $this->request->param();
                //选择了分公司
                if ($user['department_id']) {
                    $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                    if ($info) {
                        // 成功上传后 获取上传信息
                        // 输出 jpg

                        $inputFileName = ROOT_PATH . 'public/uploads/' . $thismonth = date('Ymd') . '/' . $info->getFilename();


                        $file_model['file_path'] = $inputFileName;
                        $file_model_id = $data['file_id'];
                        $batch_model['batch_name'] = $data['batch_name'];

                        $batch_model['batch_path'] = $inputFileName;
                        $batch_model_id = Db::name('CrmBatch')->insertGetId($batch_model);
                        if ($info->getExtension() == "xlsx") {
                            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
                        } else {
                            $objReader = PHPExcel_IOFactory::createReader('Excel5');
                        }
                        $objPHPExcel = $objReader->load($inputFileName);
                        $sheetDatas = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
                        unset($sheetDatas[1]);
                        $arrmobile = [];
                        foreach ($sheetDatas as $value) {
                            $arrmobile[] = trim($value['B']);
                        }

                        $findM = Db::name('CrmCustomer')->whereIn('mobile', $arrmobile)->column('mobile');
                        foreach ($sheetDatas as $value) {
                            if ($customer_model['mobile'] = $value['B'] == null) {

                            } else {
                                $customer_model['name'] = trim($value['A']);
                                $customer_model['mobile'] = trim($value['B']);
                                $customer_model['age'] = trim($value['C']);
                                $customer_model['remarks'] = trim($value['D']);
                                $customer_model['money_demand'] = trim($value['E']);
                                $customer_model['city'] = strpos($value['F'], '市') !== false ? trim($value['F']) : trim($value['F']) . '市';
                                $customer_model['customer_type_id'] = intval($value['G']);
                                $customer_model['file_id'] = $file_model_id;
                                $customer_model['batch_id'] = $batch_model_id;
                                $customer_model['department_id'] = $user['department_id'];
                                $customer_model['status'] = 1;
                                $customer_model['user_id'] = $user['id'];
                                if (in_array($customer_model['mobile'], $findM)) {
                                    continue;
                                }
                                if (strpos($customer_model['remarks'], '房') !== false) {
                                    $customer_model['is_house'] = 1;
                                } else {
                                    $customer_model['is_house'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '车') !== false) {
                                    $customer_model['is_car'] = 1;
                                } else {
                                    $customer_model['is_car'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '社保') !== false) {
                                    $customer_model['is_social'] = 1;
                                } else {
                                    $customer_model['is_social'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '公积金') !== false) {
                                    $customer_model['is_fund'] = 1;
                                } else {
                                    $customer_model['is_fund'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '保单') !== false) {
                                    $customer_model['is_insurance'] = 1;
                                } else {
                                    $customer_model['is_insurance'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '微粒贷') !== false) {
                                    $customer_model['webank'] = 1;
                                } else {
                                    $customer_model['webank'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '工资') !== false) {
                                    $customer_model['is_work'] = 1;
                                } else {
                                    $customer_model['is_work'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '信用卡') !== false) {
                                    $customer_model['is_credit'] = 1;
                                } else {
                                    $customer_model['is_credit'] = 0;
                                }
                                if (strpos($customer_model['remarks'], '企业税') !== false) {
                                    $customer_model['is_tax'] = 1;
                                } else {
                                    $customer_model['is_tax'] = 0;
                                }
				$customer_model['mobile_md5'] = md5($customer_model['mobile']);


                                $check = Db::name('CrmCustomer')->where('mobile', $customer_model['mobile'])->find();
                                if ($check) {
                                    continue;
                                } else {
                                    Db::name('CrmCustomer')->insert($customer_model);
                                }
                            }
                        }
                        $this->success('恭喜, 数据保存成功!');
                    } else {
                        // 上传失败获取错误信息
                        echo $file->getError();
                    }
                } else {
                    $this->error('请选择部门!', url('customer/customer/index2'));
                }

            } else {
                $this->error('未选择文件!', url('customer/customer/index2'));
            }
        } else {
            $file_list = Cache::store('redis')->get('file_list');
            $this->assign('file', $file_list);
            $department_list = DB::name("Department")->where("pid", 1)->select();
            $this->assign('department_list', $department_list);
            return $this->_form($this->table, 'importmy');
        }
    }

    public function comment($id)
    {
        $customer = Db::name('CrmCustomer')->find($id);
        return $this->fetch('comment', ['customer' => $customer]);
    }

    /**
     * 新增备忘
     */
    public function memo()
    {

        return $this->fetch('memo');
    }

    public function memoadd()
    {
        $data = $this->request->param();
        $user = session('user');
        $user_id = $user['id'];
        $notice_user_data['user_id'] = $user_id;
        $notice_user_data['notice_id'] = '';
        $notice_user_data['title'] = $data['title'];
        $notice_user_data['content'] = $data['content'];
        $notice_user_data['notice_time'] = $data['notice_time'];
        $notice_user_data['status'] = 0;
        $notice_user_data['type'] = 2;
        Db::name('CrmNoticeUser')->insert($notice_user_data);
        $this->success('恭喜, 数据保存成功!', '');
    }

    /**
     * 权限编辑
     */
    public function edit($id)
    {
        $user = session('user');
        $get = $this->request->get();
        if (isset($get['xh'])) {
            $xh = $get['xh'];

        } else {
            $xh = 1;
        }
        $page = 0;

        if (isset($get['page'])) {

            if ($get['page'] != "") {
                $page = $get['page'];

            } else {
                $page = 1;
            }
        }

        $customer = Db::name('CrmCustomer')->find($id);
        if ($customer['customer_type_id'] == 6 || $customer['customer_type_id'] == '') {
            $customer_type_type = 1;
        } else {
            $customer_type_type = 2;
        }
        $this->assign('customer_type_type', $customer_type_type);
        $xygsql = session('xygsql');

        $xygsqlasd = substr($xygsql, 0, strlen($xygsql) - 1) . "limit 2 )";

        $xygsqllist = Db::query("$xygsqlasd");


        $xygstatus = 0;

        if (count($xygsqllist) >= 2) {

            $qweqsd = Cookie('rows');
            if (isset($get['suan'])) {

                if ($get['suan'] == 1) {
                    $xh = ($qweqsd * ($page - 1)) + $xh;
                } else {
                }
            }
            if ($xh % $qweqsd == 0) {
                $page = $page + 1;
            }
            $this->assign('page', $page);

            $ttt = substr($xygsql, 0, strlen($xygsql) - 1) . "limit $xh,1 )";


            $listasd = Db::query("$ttt");
            if ($listasd == null) {
                $xygstatus = 0;

            } else {
                $xygstatus = 1;
                $xygid = $listasd[0]['id'];
                $xygname = $listasd[0]['name'];
                $this->assign('xygid', $xygid);
                $this->assign('xygname', $xygname);
                $this->assign('xh', $xh + 1);
            }


        } else {
            $xygstatus = 0;
        }
        $this->assign('xygstatus', $xygstatus);

        if ($user['authorize'] == 2) {
            if ($user['id'] != $customer['user_id']) {
                return $this->error("您没有权限查看");
            }
        } else if ($user['authorize'] == 6 || $user['authorize'] == 7 || $user['authorize'] == 8) {
            $department_id_list = Cache::store('redis')->get('department_tree' . $user['department_id']);

            $isin = in_array($customer['department_id'], $department_id_list);
            if (!$isin) {
                return $this->error("您没有权限查看");
            }
        }
        $user_id = $customer['user_id'];
        $user_name = Db::name('SystemUser')->where('id', $user_id)->field("name")->find();
        $house = Db::name('CrmHouse')->where('customer_id', $id)->find();
        $car = Db::name('CrmCar')->where('customer_id', $id)->find();
        $insurance = Db::name('CrmInsurance')->where('customer_id', $id)->find();
        $credit = Db::name('CrmCredit')->where('customer_id', $id)->find();
        $company = Db::name('CrmCompany')->where('customer_id', $id)->find();
        $social = Db::name('CrmSocial')->where('customer_id', $id)->find();
        $fund = Db::name('CrmFund')->where('customer_id', $id)->find();
        $work = Db::name('CrmWork')->where('customer_id', $id)->find();
        //$remark_list=Db::name('CrmRemark')->where('customer_id',$id)->order('create_time DESC')->select();
        $customer_type_list = Db::name('CrmCustomerType')->select();


        if ($customer['reset_time'] == null || $user['department_id'] == 1) {
            $allot_record = Db::name('AllotRecord')->where('customer_id', $id)->order('create_time', 'asc')->select();
            //$customer['remarks']= explode('    ',$customer['remarks']);
            //foreach($customer['remarks'] as $k => $v){
            //    $res[] = ($k+1)."：".$v;
            //}
            //$res = array_reverse($res);
            //$customer['remarks'] = implode('',$res);
            //$customer['remarks'] = '';
            $remark_list = Db::name("CrmCustomerRemark")->where('customer_id', $id)->order('create_time', 'desc')->select();
            if (!empty($remark_list)) {
                foreach ($remark_list as $remark_obj) {
                    //$customer['remarks'] = $customer['remarks'] . $remark_obj['remark'] . '-' . $remark_obj['create_time'] . "\r\n";;
                }
            }
        } else {

            $allot_record = Db::name('AllotRecord')->where('create_time', '>', $customer['reset_time'])->where('customer_id', $id)->order('create_time', 'asc')->select();
            //$customer['remarks'] = '';
            $remark_list = Db::name("CrmCustomerRemark")->where('create_time', '>', $customer['reset_time'])->where('customer_id', $id)->order('create_time', 'desc')->select();
            foreach ($remark_list as $remark_obj) {
                //$customer['remarks'] = $customer['remarks'] . $remark_obj['remark'] . "\r\n";;
            }

        }


        return $this->fetch('edit', ['social' => $social, 'fund' => $fund, 'work' => $work, 'house' => $house, 'car' => $car, 'insurance' => $insurance, 'credit' => $credit, 'customer' => $customer, 'company' => $company, "customer_type_list" => $customer_type_list, "allot_record" => $allot_record, "customer_id" => $id, "user_name" => $user_name['name']]);
    }

    public function edit1($id)
    {
        $user = session('user');

        $customer = Db::name('CrmCustomer')->find($id);

        // if($user['authorize']==2){
        //     if($user['id']!=$customer['user_id']){
        //         return $this->error("您没有权限查看");
        //     }
        // }else if($user['authorize']==6||$user['authorize']==7||$user['authorize']==8){
        //     $department_id_list=Cache::store('redis')->get('department_tree'.$user['department_id']);
        //     $isin = in_array($customer['department_id'],$department_id_list);
        //     if(!$isin){
        //         return $this->error("您没有权限查看");
        //     }
        // }
        $user_id = $customer['user_id'];
        $user_name = Db::name('SystemUser')->where('id', $user_id)->field("name")->find();
        $house = Db::name('CrmHouse')->where('customer_id', $id)->find();
        $car = Db::name('CrmCar')->where('customer_id', $id)->find();
        $insurance = Db::name('CrmInsurance')->where('customer_id', $id)->find();
        $credit = Db::name('CrmCredit')->where('customer_id', $id)->find();
        $company = Db::name('CrmCompany')->where('customer_id', $id)->find();
        $social = Db::name('CrmSocial')->where('customer_id', $id)->find();
        $fund = Db::name('CrmFund')->where('customer_id', $id)->find();
        $work = Db::name('CrmWork')->where('customer_id', $id)->find();
        //$remark_list=Db::name('CrmRemark')->where('customer_id',$id)->order('create_time DESC')->select();
        // print_r($customer);
        $customer_type_list = Db::name('CrmCustomerType')->select();
        if ($customer['reset_time'] == null || $user['department_id'] == 1) {
            $allot_record = Db::name('AllotRecord')->where('customer_id', $id)->order('create_time', 'asc')->select();
            $remk = explode('    ', $customer['remarks']);
            $remks = array_reverse($remk);
            $customer['remarks'] = implode('', $remks);
            // print_r($remks);exit;

        } else {

            $allot_record = Db::name('AllotRecord')->where('create_time', '>', $customer['reset_time'])->where('customer_id', $id)->order('create_time', 'asc')->select();
            $customer['remarks'] = '';
            $remark_list = Db::name("CrmCustomerRemark")->where('create_time', '>', $customer['reset_time'])->where('customer_id', $id)->order('create_time', 'desc')->select();
            foreach ($remark_list as $remark_obj) {
                $customer['remarks'] = $customer['remarks'] . $remark_obj['remark'] . "\r\n";
            }

        }

        session('index1_location_id', $id);

        return $this->fetch('edit1', ['social' => $social, 'fund' => $fund, 'work' => $work, 'house' => $house, 'car' => $car, 'insurance' => $insurance, 'credit' => $credit, 'customer' => $customer, 'company' => $company, "customer_type_list" => $customer_type_list, "allot_record" => $allot_record, "customer_id" => $id, "user_name" => $user_name['name']]);
    }

    public function isdel($id)
    {
        $res = Db::name('CrmCustomer')->where('id', $id)->update(array('is_deleted' => 1));
        if ($res) {
            return json(['code' => 1, 'msg' => '成功删除']);
        } else {
            return json(['code' => 0, 'msg' => '成功失败']);
        }

    }

    public function isdelremark($id)
    {
        Db::name('CrmCustomerRemark')->where('customer_id', $id)->delete();
        Db::name('AllotRecord')->where('customer_id', $id)->delete();
        $res = Db::name('CrmCustomer')->where('id', $id)->update(array('remarks' => ''));
        if ($res) {
            return json(['code' => 1, 'msg' => '成功清除']);
        } else {
            return json(['code' => 0, 'msg' => '清除失败']);
        }

    }

    public function edit2($id)
    {
        $user = session('user');

        $customer = Db::name('CrmCustomer')->find($id);
        if ($user['authorize'] == 2) {
            if ($user['id'] != $customer['user_id']) {
                return $this->error("您没有权限查看");
            }
        } else if ($user['authorize'] == 6 || $user['authorize'] == 7 || $user['authorize'] == 8) {
            $department_id_list = Cache::store('redis')->get('department_tree' . $user['department_id']);
            $isin = in_array($customer['department_id'], $department_id_list);
            if (!$isin) {
                return $this->error("您没有权限查看");
            }
        }
        $user_id = $customer['user_id'];
        $user_name = Db::name('SystemUser')->where('id', $user_id)->field("name")->find();
        $house = Db::name('CrmHouse')->where('customer_id', $id)->find();
        $car = Db::name('CrmCar')->where('customer_id', $id)->find();
        $insurance = Db::name('CrmInsurance')->where('customer_id', $id)->find();
        $credit = Db::name('CrmCredit')->where('customer_id', $id)->find();
        $company = Db::name('CrmCompany')->where('customer_id', $id)->find();
        $social = Db::name('CrmSocial')->where('customer_id', $id)->find();
        $fund = Db::name('CrmFund')->where('customer_id', $id)->find();
        $work = Db::name('CrmWork')->where('customer_id', $id)->find();
        //$remark_list=Db::name('CrmRemark')->where('customer_id',$id)->order('create_time DESC')->select();
        $customer_type_list = Db::name('CrmCustomerType')->select();

        if ($customer['reset_time'] == null || $user['department_id'] == 1) {
            $allot_record = Db::name('AllotRecord')->where('customer_id', $id)->order('create_time', 'asc')->select();

        } else {

            $allot_record = Db::name('AllotRecord')->where('create_time', '>', $customer['reset_time'])->where('customer_id', $id)->order('create_time', 'asc')->select();
            $customer['remarks'] = '';
            $remark_list = Db::name("CrmCustomerRemark")->where('create_time', '>', $customer['reset_time'])->where('customer_id', $id)->order('create_time', 'desc')->select();
            foreach ($remark_list as $remark_obj) {
                $customer['remarks'] = $customer['remarks'] . $remark_obj['remark'] . "\r\n";;
            }

        }

        return $this->fetch('edit2', ['social' => $social, 'fund' => $fund, 'work' => $work, 'house' => $house, 'car' => $car, 'insurance' => $insurance, 'credit' => $credit, 'customer' => $customer, 'company' => $company, "customer_type_list" => $customer_type_list, "allot_record" => $allot_record, "customer_id" => $id, "user_name" => $user_name['name']]);

    }

    //信息修改
    public function update()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
//            print_r($data);exit;
            $id = $data['id'];
            $customer_model['name'] = $data['name'];
            $customer_model['mobile'] = $data['mobile'];
            $customer_model['phone'] = $data['phone'];
            $customer_model['place'] = $data['place'];
            $customer_model['is_marry'] = $data['is_marry'];
            $customer_model['is_know'] = $data['is_know'];
            $customer_model['money_demand'] = $data['money_demand'];
            $customer_model['credit_score'] = $data['credit_score'];
            $customer_model['webank'] = $data['webank'];
            $customer_model['update_time'] = date('y-m-d H:i:s', time());
            $customer_model['last_time'] = date('y-m-d H:i:s', time());
            $customer_model['sex'] = $data['sex'];
            $customer_model['age'] = $data['age'];
            $customer_model['is_house'] = $data['is_house'];
            $customer_model['is_car'] = $data['is_car'];
            $customer_model['is_credit'] = $data['is_credit'];
            $customer_model['is_insurance'] = $data['is_insurance'];
            $customer_model['is_work'] = $data['is_work'];
            $customer_model['is_fund'] = $data['is_fund'];
            $customer_model['is_reassign'] = $data['is_reassign'];
            //注释上线需要把1删除 is_reassign
            $user = session('user');
            $user_id = $user['id'];

            if (isset($data['is_remind'])) {
                $customer_model['is_remind'] = 1;
                $customer_model['notice_time'] = $data['notice_time'];


                $notice_user_data['user_id'] = $user_id;
                $notice_user_data['notice_id'] = '';
                $notice_user_data['title'] = $data['name'];
                $notice_user_data['content'] = $data['notice_content'] . '.';;
                $notice_user_data['notice_time'] = $data['notice_time'];
                $notice_user_data['status'] = 0;
                $notice_user_data['type'] = 2;
                Db::name('CrmNoticeUser')->insert($notice_user_data);
            } else {
                Db::name('CrmNoticeUser')->where('title', $data['name'])->where('user_id', $user_id)->delete();
                $customer_model['is_remind'] = 0;
            }
            if (isset($data['is_company'])) {
                $customer_model['is_company'] = 1;
            } else {
                $customer_model['is_company'] = 0;
            }
            if (isset($data['is_social'])) {
                $customer_model['is_social'] = 1;
            } else {
                $customer_model['is_social'] = 0;
            }


            $customer_model['new_data'] = 2;
            Db::name('CrmCustomer')->where('id', $data['id'])->update($customer_model);


            if (isset($data['is_house'])) {
                $house_list = Db::name('CrmHouse')->where('customer_id', $id)->select();

                if (count($house_list) > 0) {
                    $house_model = $house_list[0];
                    $house_model['house_type'] = $data['house_type'];
                    $house_model['area'] = $data['area'];
                    $house_model['house_value'] = $data['house_value'];
                    $house_model['house_times'] = $data['house_times'];
                    $house_model['house_month_pay'] = $data['house_month_pay'];
                    $house_model['house_bank_name'] = $data['house_bank_name'];
                    Db::name('CrmHouse')->where('id', $house_model['id'])->update($house_model);
                } else {
                    $house_model['house_type'] = $data['house_type'];
                    $house_model['area'] = $data['area'];
                    $house_model['house_value'] = $data['house_value'];
                    $house_model['house_times'] = $data['house_times'];
                    $house_model['house_month_pay'] = $data['house_month_pay'];
                    $house_model['house_bank_name'] = $data['house_bank_name'];
                    $house_model['create_time'] = date('y-m-d H:i:s', time());
                    $house_model['customer_id'] = $id;
                    Db::name('CrmHouse')->insert($house_model);
                }
            }
            if (isset($data['is_car'])) {
                $car_list = Db::name('CrmCar')->where('customer_id', $id)->select();
                if (count($car_list) > 0) {
                    $car_model = $car_list[0];
                    $car_model['car_type'] = $data['car_type'];
                    $car_model['time_limit'] = $data['time_limit'];
                    $car_model['car_bank_name'] = $data['car_bank_name'];
                    $car_model['car_month_pay'] = $data['car_month_pay'];
                    $car_model['car_times'] = $data['car_times'];
                    $car_model['car_value'] = $data['car_value'];


                    Db::name('CrmCar')->where('id', $car_model['id'])->update($car_model);
                } else {
                    $car_model['car_type'] = $data['car_type'];
                    $car_model['time_limit'] = $data['time_limit'];
                    $car_model['car_bank_name'] = $data['car_bank_name'];
                    $car_model['car_month_pay'] = $data['car_month_pay'];
                    $car_model['car_times'] = $data['car_times'];
                    $car_model['car_value'] = $data['car_value'];
                    $car_model['create_time'] = date('y-m-d H:i:s', time());
                    $car_model['customer_id'] = $id;
                    Db::name('CrmCar')->insert($car_model);
                }
            }
            if (isset($data['is_credit'])) {
                $credit_list = Db::name('CrmCredit')->where('customer_id', $id)->select();
                if (count($credit_list) > 0) {
                    $credit_model = $credit_list[0];
                    $credit_model['card_amount'] = $data['card_amount'];
                    $credit_model['is_coverdue'] = $data['is_coverdue'];
                    $credit_model['loan_amount'] = $data['loan_amount'];
                    $credit_model['is_loverdue'] = $data['is_loverdue'];
                    Db::name('CrmCredit')->where('id', $credit_model['id'])->update($credit_model);
                } else {
                    $credit_model['card_amount'] = $data['card_amount'];
                    $credit_model['is_coverdue'] = $data['is_coverdue'];
                    $credit_model['loan_amount'] = $data['loan_amount'];
                    $credit_model['is_loverdue'] = $data['is_loverdue'];
                    $credit_model['create_time'] = date('y-m-d H:i:s', time());
                    $credit_model['customer_id'] = $id;
                    Db::name('CrmCredit')->insert($credit_model);
                }
            }
            if (isset($data['is_company'])) {
                $company_list = Db::name('CrmCompany')->where('customer_id', $id)->select();
                if (count($company_list) > 0) {
                    $company_model = $company_list[0];
                    $company_model['licence_years'] = $data['licence_years'];
                    $company_model['is_legal'] = $data['is_legal'];
                    Db::name('CrmCompany')->where('id', $company_model['id'])->update($company_model);
                } else {
                    $company_model['licence_years'] = $data['licence_years'];
                    $company_model['is_legal'] = $data['is_legal'];
                    $company_model['create_time'] = date('y-m-d H:i:s', time());
                    $company_model['customer_id'] = $id;
                    Db::name('CrmCompany')->insert($company_model);
                }
            }
            if (isset($data['is_insurance'])) {
                $insurance_list = Db::name('CrmInsurance')->where('customer_id', $id)->select();
                if (count($insurance_list) > 0) {
                    $insurance_model = $insurance_list[0];
                    $insurance_model['insurance_pay_type'] = $data['insurance_pay_type'];
                    $insurance_model['remark'] = $data['remark'];
                    $insurance_model['company'] = $data['company'];
                    Db::name('CrmInsurance')->where('id', $insurance_model['id'])->update($insurance_model);
                } else {
                    $insurance_model['insurance_pay_type'] = $data['insurance_pay_type'];
                    $insurance_model['remark'] = $data['remark'];
                    $insurance_model['company'] = $data['company'];
                    $insurance_model['create_time'] = date('y-m-d H:i:s', time());
                    $insurance_model['customer_id'] = $id;
                    Db::name('CrmInsurance')->insert($insurance_model);
                }
            }
            if (isset($data['is_social'])) {
                $social_list = Db::name('CrmSocial')->where('customer_id', $id)->select();

                if (count($social_list) > 0) {
                    $social_model = $social_list[0];
                    $social_model['social_years'] = $data['social_years'];
                    $social_model['social_money'] = $data['social_money'];
                    Db::name('CrmSocial')->where('id', $social_model['id'])->update($social_model);
                } else {
                    $social_model['social_years'] = $data['social_years'];
                    $social_model['social_money'] = $data['social_money'];
                    $social_model['create_time'] = date('y-m-d H:i:s', time());
                    $social_model['customer_id'] = $id;
                    Db::name('CrmSocial')->insert($social_model);
                }

            }
            if (isset($data['is_fund'])) {
                $fund_list = Db::name('CrmFund')->where('customer_id', $id)->select();

                if (count($fund_list) > 0) {
                    $fund_model = $fund_list[0];
                    $fund_model['fund_years'] = $data['fund_years'];
                    $fund_model['fund_money'] = $data['fund_money'];
                    Db::name('CrmFund')->where('id', $fund_model['id'])->update($fund_model);
                } else {
                    $fund_model['fund_years'] = $data['fund_years'];
                    $fund_model['fund_money'] = $data['fund_money'];
                    $fund_model['create_time'] = date('y-m-d H:i:s', time());
                    $fund_model['customer_id'] = $id;
                    Db::name('CrmFund')->insert($fund_model);
                }
            }
            if (isset($data['is_work'])) {
                $work_list = Db::name('CrmWork')->where('customer_id', $id)->select();
                if (count($work_list) > 0) {
                    $work_model = $work_list[0];
                    $work_model['company_nature'] = $data['company_nature'];
                    $work_model['money'] = $data['money'];
                    $work_model['pay_type'] = $data['pay_type'];
                    Db::name('CrmWork')->where('id', $work_model['id'])->update($work_model);
                } else {
                    $work_model['company_nature'] = $data['company_nature'];
                    $work_model['money'] = $data['money'];
                    $work_model['pay_type'] = $data['pay_type'];
                    $work_model['create_time'] = date('y-m-d H:i:s', time());
                    $work_model['customer_id'] = $id;
                    Db::name('CrmWork')->insert($work_model);
                }
            }
            $this->success('modal|恭喜, 数据保存成功!', '');
        }
    }

    //评论提交
    public function update1()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            // print_r($data);exit;
            $user = session('user');
            $customer_id = $data['id'];
            $list = Db::query("SELECT * from crm_customer where id=$customer_id");
            $remarks = $list[0]['comment'];
            // $wish_remarks=$list[0]['wish_remarks'];

            if ($data['comment']) {
                if ($data['comment']) {
                    $customer_model['comment'] = $remarks . '' . $data['comment'] . date('Y-m-d H:i:s', time()) . $user['name'];
                }
                // if($data['wish_remarks']){
                //     $customer_model['wish_remarks']=$wish_remarks.''.$data['wish_remarks'].date('Y-m-d H:i:s',time()).$user['name'];
                // }

                $customer_model['quota'] = $data['quota'];
                $customer_model['status'] = $data['status'];
                $customer_model['customer_type_id'] = $data['customer_type_id'];
                $customer_model['channel'] = $data['channel'];
                // print_r($customer_model);exit;
                Db::name('CrmCustomer')->where('id', $data['id'])->update($customer_model);
                $this->success('modal|恭喜, 数据保存成功!', '');
            }


        }

    }

    //评论提交
    public function update3()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();

            $user = session('user');
            $customer_id = $data['id'];
            $list = Db::query("SELECT * from crm_customer where id=$customer_id");
            $remarks = $list[0]['remarks'];
            $customer_model['status'] = $data['status'];
            $customer_model['new_data'] = 2;
            $customer_model['update_time'] = date('y-m-d H:i:s', time());
            $customer_model['last_time'] = date('y-m-d H:i:s', time());
            $customer_model['customer_type_id'] = $data['customer_type_id'];
            $customer_model['quota'] = $data['quota'];
            $customer_model['channel'] = $data['channel'];
            $customer_model['is_reassign'] = $data['is_reassign'];
            $user = session('user');
            $user_id = $user['id'];
            if (isset($data['is_remind'])) {
                $customer_model['is_remind'] = 1;
                $customer_model['notice_time'] = $data['notice_time'];

                $notice_user_data['user_id'] = $user_id;
                $notice_user_data['notice_id'] = '';
                $notice_user_data['title'] = $data['name'];
                $notice_user_data['content'] = $data['notice_content'] . '.';
                $notice_user_data['notice_time'] = $data['notice_time'];
                $notice_user_data['status'] = 0;
                $notice_user_data['type'] = 2;
                $notice_user_data['url'] = "customer/customer/edit?id=$customer_id&ppx=1";
                Db::name('CrmNoticeUser')->insert($notice_user_data);
            } else {
                Db::name('CrmNoticeUser')->where('title', $data['name'])->where('user_id', $user_id)->delete();
                $customer_model['is_remind'] = 0;
            }

            $customer_model['remark_times'] = $list[0]['remark_times'] + 1;
            $user = session('user');
            if ($data['remarks'] == "") {

            } else {
                if ($remarks == null) {
                    $customer_model['remark_time'] = date('Y-m-d H:i:s', time());
                    $customer_model['remarks'] = '1.' . $customer_model['remark_time'] . $user['name'] . '：' . $data['remarks'] . "\r\n";
                } else {
                    $customer_model['remark_time'] = date('Y-m-d H:i:s', time());
                    $customer_model['remarks'] = $customer_model['remark_times'] . '.' . $customer_model['remark_time'] . $user['name'] . '：' . $data['remarks'] . "\r\n" . $remarks;
                }


            }
//            $wish_remarks=$list[0]['wish_remarks'];
//            if($data['wish_remarks'] != ""){
//                $customer_model['wish_remarks'] = $wish_remarks . '
//                ' . $data['wish_remarks'] . date('Y-m-d H:i:s', time()) . $user['name'];
//            }

            Db::name('CrmCustomer')->where('id', $data['id'])->update($customer_model);


            $statistics['customer_type_id'] = $data['customer_type_id'];
            Db::name('AllotRecord')->where('type', 1)->where('customer_id', $customer_id)->update($statistics);

            $crm_customer_remark['customer_id'] = $customer_id;
            $crm_customer_remark['remark'] = $user['name'] . '：' . $data['remarks'];
            Db::name('CrmCustomerRemark')->insert($crm_customer_remark);
            $customer_sample = Db::name("CrmCustomer")->find($customer_id);


            $this->success('modal|恭喜, 数据保存成功!', '');


        }
    }

    public function remark()
    {
        $data = $this->request->param();

        if (isset($data['remark'])) {

            $customer_model['id'] = $data['id'];
            $customer_model['last_time'] = date("Y-m-d H:i:s");
            if (isset($data['customer_type_id'])) {

                $customer_model['customer_type_id'] = $data['customer_type_id'];

            }
            if (isset($data['status'])) {
                $customer_model['status'] = $data['status'];
            }
            Db::name('CrmCustomer')->where('id', $data['id'])->update($customer_model);
            $remark_model['remark'] = $data['remark'];
            $remark_model['create_time'] = date('y-m-d H:i:s', time());
            $remark_model['customer_id'] = $data['id'];
            Db::name('CrmRemark')->insert($remark_model);
        } else {
            $customer_model['id'] = $data['id'];
            $customer_model['last_time'] = date("Y-m-d H:i:s");
            if (isset($data['customer_type_id'])) {

                $customer_model['customer_type_id'] = $data['customer_type_id'];

            }
            if (isset($data['status'])) {
                $customer_model['status'] = $data['status'];
            }
            Db::name('CrmCustomer')->where('id', $data['id'])->update($customer_model);


        }
        $this->success('保存成功', '');

    }

    /**
     * 权限删除
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $id = $this->request->post('id');
            Db::name('CrmHouse')->where('customer_id', $id)->delete();
            Db::name('CrmCar')->where('customer_id', $id)->delete();
            Db::name('CrmInsurance')->where('customer_id', $id)->delete();
            Db::name('CrmCredit')->where('customer_id', $id)->delete();
            Db::name('CrmCompany')->where('customer_id', $id)->delete();
            Db::name('Statistics')->where('customer_id', $id)->delete();
            $this->success("modal|客户删除成功！", '');
        }
        $this->error("客户删除失败，请稍候再试！");
    }

    //手动加入公共池2
    public function is_public2()
    {
        $request = Request::instance();
        $ids = explode(',', $request->post('id', ''));
        if ($ids != '') {
            //$ids = explode(',', $ids);
            $ids = array_unique($ids);
            foreach ($ids as $id) {
                $customer = Db::name('CrmCustomer')->find($id);
                $s_data['type'] = 4;
                $s_data['source_id'] = $customer['user_id'];
                $source = Db::name("SystemUser")->find($s_data['source_id']);
                $s_data['user_id'] = 0;
                $user = session('user');
                $s_data['operate_id'] = $user['id'];
                $s_data['customer_id'] = $id;
                $s_data['content'] = '数据由' . $source['name'] . '手动加入至公共池，操作人' . $user['name'];
                Db::name('AllotRecord')->insert($s_data);

                $customer_model['user_id'] = null;
                $customer_model['is_public'] = 1;
                $customer_model['is_quit'] = null;
                $customer_model['is_rubbish'] = null;
                Db::name('CrmCustomer')->where('id', $id)->update($customer_model);
            }
            $this->success('加入成功');
        } else {
            $this->error('请选择需要操作的数据');
        }
        // 提交事务
    }

    //手动加入公共池
    public function is_public()
    {
        $request = Request::instance();
        $ids = explode(',', $request->post('id', ''));
        if ($ids != '') {
            //$ids = explode(',', $ids);
            $ids = array_unique($ids);
            foreach ($ids as $id) {
                $customer = Db::name('CrmCustomer')->find($id);
                $s_data['type'] = 4;
                $s_data['source_id'] = $customer['user_id'];
                $source = Db::name("SystemUser")->find($s_data['source_id']);
                $s_data['user_id'] = 0;
                $user = session('user');
                $s_data['operate_id'] = $user['id'];
                $s_data['customer_id'] = $id;
                $s_data['content'] = '数据由' . $source['name'] . '手动加入至公共池，操作人' . $user['name'];
                Db::name('AllotRecord')->insert($s_data);

                $customer_model['user_id'] = null;
                $customer_model['is_public'] = 1;
                $customer_model['is_quit'] = null;
                $customer_model['is_rubbish'] = null;
                Db::name('CrmCustomer')->where('id', $id)->update($customer_model);
            }
            $this->success('modal|加入成功');
        } else {
            $this->error('modal|请选择需要操作的数据');
        }
        // 提交事务
    }

    //数据分配到机构组-弃用
    public function allot1()
    {
        $request = Request::instance();
        $ids = explode(',', $request->post('id', ''));

        if (!empty($ids)) {
            $db = Db::name('Department')->whereNull('is_deleted');

            $user = session('user');
            $db->where('pid', $user['department_id']);
            $department_list = $db->select();
            return $this->fetch('allot1', ['department_list' => $department_list]);

        } else {
            $this->error('请选择需要操作的数据');
        }

    }

    //随机分配-团队客户用
    public function random_allot()
    {
        $db = Db::name('SystemUser');
        $request = Request::instance();
        $ids = explode(',', $request->post('id', ''));
        $user = session('user');
        $department_id = $user['department_id'];
        if (!empty($ids)) {
            $data = $request->get();
            if (isset($data['department_id']) && $data['department_id'] !== "") {
                $department_list = Cache::store('redis')->get('department_tree' . $data['department_id']);
                $admin_user_list = $db->where('department_id', 'in', $department_list)->where('is_deleted', 0)->select();
            } else {
                $admin_user_list = Cache::store('redis')->get('department_user' . $department_id);
            }
            $department_list = Cache::store('redis')->get('operation_departemnt' . $user['id']);
            $department_list = ToolsService::arr2table($department_list);
            $this->assign('department_list', $department_list);

            return $this->fetch('random_allot', ['admin_user_list' => $admin_user_list]);

        } else {
            $this->error('请选择需要操作的数据');
        }


    }

    public function tempfenpei()
    {
    }

    public function to_my()
    {
        $request = Request::instance();
        $ids = explode(',', $request->post('id', ''));
        $allot_time = date('Y-m-d H:i:s', time());
        $user = session('user');
        $userid = $user['id'];
        if ($ids != '') {
            foreach ($ids as $id) {
                $customer = Db::name("CrmCustomer")->find($id);
                if ($userid == $customer['user_id']) {
                    $customer_model['is_reassign'] = null;
                    $customer_model['allot_time'] = $allot_time;
                    $customer_model['redistribution_time'] = $allot_time;
                    $customer_model['last_time'] = $allot_time;
                    Db::name("CrmCustomer")->where('id', $id)->update($customer_model);
                } else {
                    $customer_model['is_reassign'] = null;
                    $customer_model['allot_time'] = $allot_time;
                    $customer_model['redistribution_time'] = $allot_time;
                    $customer_model['last_time'] = $allot_time;
                    $customer_model['user_id'] = $userid;
                    Db::name("CrmCustomer")->where('id', $id)->update($customer_model);
                }
            }
            $this->success('操作成功');
        } else {
            $this->error('请选择需要操作的数据');
        }
    }

    public function randomToMy($ids = '')
    {
        $allot_time = date('Y-m-d H:i:s', time());
        $user = session('user');
        $userid = $user['id'];
//        print_r($user);exit;
        if ($ids != '') {
            $i = 0;
            foreach ($ids as $id) {
                $customer = Db::name("CrmCustomer")->find($id);
                if ($userid == $customer['user_id']) {
                    $customer_model['is_reassign'] = null;
                    $customer_model['allot_time'] = $allot_time;
                    $customer_model['redistribution_time'] = $allot_time;
                    $customer_model['last_time'] = $allot_time;
                    Db::name("CrmCustomer")->where('id', $id)->update($customer_model);
                } else {
                    $customer_model['is_reassign'] = null;
                    $customer_model['allot_time'] = $allot_time;
                    $customer_model['redistribution_time'] = $allot_time;
                    $customer_model['last_time'] = $allot_time;
                    $customer_model['user_id'] = $userid;
                    Db::name("CrmCustomer")->where('id', $id)->update($customer_model);
                }
            }
            return json(['data' => '成功分配'], 200);
        } else {
            return json(['data' => '请选择需要操作的数据'], 200);
        }
    }

    //团队客户随机分配
    public function randomAllotTox($user_ids = '', $ids = '')
    {

        $allot_time = date('Y-m-d H:i:s', time());
        $user = session('user');
        $uuuu = $user['department_id'];
        // 启动事务
        // return json(['data' => '暂时关闭！'], 200);
        if ($ids != '' && $user_ids != '') {
            if ($user['authorize'] == 2 && $user_ids[0] != $user['id']) {
                return json(['data' => '你没有权限分配数据给他人'], 200);

            }

	    $count = Db::name("CrmCustomer")->where('user_id', $user['id'])->where('status', '<>', 5)->count();
            if ($user_ids[0] == $user['id']) {
                if ($user['authorize'] == 2 && $count >= 500) {
                    return json(['data' => '已超过500条！'], 200);
                }
                if ($user['authorize'] == 8 && $count >= 1000) {
                    return json(['data' => '已超过100条！'], 200);
                }
            }

            $cg = 0;
            $sb = 0;
            $i = 0;
            $user_count = count($user_ids);
            $not_id = '0';

            foreach ($ids as $id) {
                $user_id = $user_ids[$i % $user_count];
                $customer = Db::name("CrmCustomer")->find($id);
                // print_r($customer);exit;
                if ($customer['user_id'] == $user_id) {
                    $i++;
                    $user_id = $user_ids[$i % $user_count];

                }
                if ($customer != null) {
                    $customer_id = $customer['id'];
                    $systemUser = Db::name("SystemUser")->find($user_id);
                    $department_id = $systemUser['department_id'];
                    if ($uuuu == 1) {


                        $user_department_id = $systemUser['company_id'];
                        $customer_department_id = $this->getParentId($customer['department_id']);

                        if ($user_department_id == $customer_department_id || $customer['department_id'] == null) {
                            if ($systemUser['authorize'] == 11 || $systemUser['authorize'] == 13 || $systemUser['authorize'] == 6) {
                                $count = Db::name("CrmCustomer")->where('user_id', $user_id)->count();
                                if ($count >= 5000) {
                                    continue;
                                }
                            } elseif ($systemUser['authorize'] == 8) {
                                $count = Db::name("CrmCustomer")->where('user_id', $user_id)->count();
                                if ($count >= 500) {
                                    continue;
                                }
                            } else {
                                $count = Db::name("CrmCustomer")->where('user_id', $user_id)->count();
                                if ($count >= 300) {
                                    continue;
                                }
                            }

                            $cg = $cg + 1;
                            $customer_model['user_id'] = $user_id;
                            $customer_model['allot_time'] = $allot_time;
                            $customer_model['redistribution_time'] = $allot_time;
                            $customer_model['last_time'] = $allot_time;
                            $customer_model['department_id'] = $department_id;
                            $customer_model['new_data'] = 1;
                            $customer_model['is_reassign'] = 1;
                            $customer_model['is_public'] = null;
                            $customer_model['is_quit'] = null;
                            $customer_model['is_rubbish'] = null;
                            Db::name('CrmCustomer')->where('id', $customer_id)->update($customer_model);


                            if ($customer['user_id'] == '') {
                                $s_data['type'] = 3;
                                $s_data['source_id'] = 0;
                                $source['name'] = '公共池';
                            } else {
                                $s_data['type'] = 2;
                                $s_data['source_id'] = $customer['user_id'];
                                $source = Db::name("SystemUser")->find($s_data['source_id']);
                            }

                            $s_data['user_id'] = $user_id;
                            $s_data['operate_id'] = $user['id'];
                            $s_data['customer_id'] = $id;
                            $s_data['department_id'] = $department_id;
                            $s_data['content'] = '数据由' . $source['name'] . '分配至' . $systemUser['name'] . '，操作人' . $user['name'];
                            Db::name('AllotRecord')->insert($s_data);


                            $not_id = $not_id . ',' . $customer_id;

                        } else {
                            $sb = $sb + 1;

                            $not_id = $not_id . ',' . $customer_id;
                        }
                    } else {
                        if ($systemUser['authorize'] == 11 || $systemUser['authorize'] == 13 || $systemUser['authorize'] == 6) {
                            $count = Db::name("CrmCustomer")->where('user_id', $user_id)->count();
                            if ($count >= 5000) {
                                continue;
                            }

                        } elseif ($systemUser['authorize'] == 8) {
                            $count = Db::name("CrmCustomer")->where('user_id', $user_id)->count();
                            if ($count >= 500) {
                                continue;
                            }
                        } else {
                            $count = Db::name("CrmCustomer")->where('user_id', $user_id)->count();
                            if ($count >= 300) {
                                continue;
                            }
                        }
                        $cg = $cg + 1;
                        $customer_model['user_id'] = $user_id;
                        $customer_model['allot_time'] = $allot_time;
                        $customer_model['last_time'] = $allot_time;
                        $customer_model['redistribution_time'] = $allot_time;
                        $customer_model['department_id'] = $department_id;
                        $customer_model['new_data'] = 1;
                        $customer_model['is_reassign'] = 1;
                        $customer_model['is_public'] = null;
                        $customer_model['is_quit'] = null;
                        $customer_model['is_rubbish'] = null;
                        Db::name('CrmCustomer')->where('id', $customer_id)->update($customer_model);


                        if ($customer['user_id'] == '') {
                            $s_data['type'] = 3;
                            $s_data['source_id'] = 0;
                            $source['name'] = '公共池';
                        } else {
                            $s_data['type'] = 2;
                            $s_data['source_id'] = $customer['user_id'];
                            $source = Db::name("SystemUser")->find($s_data['source_id']);
                        }
                        $s_data['user_id'] = $user_id;
                        $s_data['operate_id'] = $user['id'];
                        $s_data['customer_id'] = $id;
                        $s_data['department_id'] = $department_id;
                        $s_data['content'] = '数据由' . $source['name'] . '分配至' . $systemUser['name'] . '，操作人' . $user['name'];
                        Db::name('AllotRecord')->insert($s_data);
                    }

                } else {

                }
                //$customers=Db::query("select * from crm_customer where (id in ($id_s) and id not in ($not_id)) and (user_id <>$user_id or user_id is null) limit 1");
                // $customer=$customers[0];
                //$customer=Db::name('CrmCustomer')->where('id','in',$ids)->where('id','not in',$not_id)->whereOr('user_id','<>',$user_id)->whereOr('user_id')->find();

                $i++;
            }


            $datetime = date('Y-m-d H:i:s', time());
            $notice_user_data = [];
            $notice_user_data['user_id'] = $user_id;
            $notice_user_data['notice_id'] = '';
            $notice_user_data['title'] = '您有新的消息';
            $notice_user_data['content'] = '你有新的客户数据导入';
            $notice_user_data['url'] = '/customer/customer/index';
            $notice_user_data['notice_time'] = $datetime;
            $notice_user_data['status'] = 0;
            $notice_user_data['type'] = 2;
            Db::name('CrmNoticeUser')->insert($notice_user_data);

            if ($sb == 0) {
                return json(['data' => '成功分配' . $cg . '条数据!'], 200);
            } else {
                return json(['data' => '成功分配' . $cg . '条数据!' . $sb . '条数据因跨分公司分配失败!'], 200);
            }

        } else {
            return json(['data' => '请选择需要操作的数据'], 200);

        }
        // 提交事务

    }


    //团队客户随机分配
    public function randomAllotTo($user_ids = '', $ids = '')
    {

        $allot_time = date('Y-m-d H:i:s', time());
        $user = session('user');
        // 启动事务

        if ($ids != '' && $user_ids != '') {

            $user_list = explode(',', $user_ids);
            $ids = explode(',', $ids);

            $cg = 0;
            $sb = 0;
            $i = 0;
            $user_count = count($user_list);
            $not_id = '0';

            foreach ($ids as $id) {
                $user_id = $user_list[$i % $user_count];
                $customer = Db::name("CrmCustomer")->find($id);
                if ($customer['user_id'] == $user_id) {
                    $i++;
                    $user_id = $user_list[$i % $user_count];

                }
                if ($customer != null) {
                    $customer_id = $customer['id'];
                    $systemUser = Db::name("SystemUser")->find($user_id);
                    $department_id = $systemUser['department_id'];
                    if ($user['department_id'] == 1) {
                        $user_department_id = $systemUser['company_id'];
                        $customer_department_id = $this->getParentId($customer['department_id']);
                        if ($user_department_id == $customer_department_id || $customer['department_id'] == null) {
                            $cg = $cg + 1;
                            $customer_model['user_id'] = $user_id;
                            $customer_model['allot_time'] = $allot_time;
                            $customer_model['redistribution_time'] = $allot_time;
                            $customer_model['last_time'] = $allot_time;
                            $customer_model['department_id'] = $department_id;
                            $customer_model['new_data'] = 1;
                            $customer_model['is_reassign'] = 1;
                            $customer_model['is_public'] = null;
                            $customer_model['is_quit'] = null;
                            $customer_model['is_rubbish'] = null;
                            Db::name('CrmCustomer')->where('id', $customer_id)->update($customer_model);


                            $s_data['type'] = 2;
                            $s_data['source_id'] = $customer['user_id'];
                            $source = Db::name("SystemUser")->find($s_data['source_id']);
                            $s_data['user_id'] = $user_id;
                            $s_data['operate_id'] = $user['id'];
                            $s_data['customer_id'] = $id;
                            $s_data['department_id'] = $department_id;
                            $s_data['content'] = '数据由' . $source['name'] . '分配至' . $systemUser['name'] . '，操作人' . $user['name'];
                            Db::name('AllotRecord')->insert($s_data);


                            $not_id = $not_id . ',' . $customer_id;

                        } else {
                            $sb = $sb + 1;

                            $not_id = $not_id . ',' . $customer_id;
                        }
                    } else {
                        $cg = $cg + 1;
                        $customer_model['user_id'] = $user_id;
                        $customer_model['allot_time'] = $allot_time;
                        $customer_model['last_time'] = $allot_time;
                        $customer_model['redistribution_time'] = $allot_time;
                        $customer_model['department_id'] = $department_id;
                        $customer_model['new_data'] = 1;
                        $customer_model['is_reassign'] = 1;
                        $customer_model['is_public'] = null;
                        $customer_model['is_quit'] = null;
                        $customer_model['is_rubbish'] = null;
                        Db::name('CrmCustomer')->where('id', $customer_id)->update($customer_model);


                        $s_data['type'] = 2;
                        $s_data['source_id'] = $customer['user_id'];
                        $source = Db::name("SystemUser")->find($s_data['source_id']);
                        $s_data['user_id'] = $user_id;
                        $s_data['operate_id'] = $user['id'];
                        $s_data['customer_id'] = $id;
                        $s_data['department_id'] = $department_id;
                        $s_data['content'] = '数据由' . $source['name'] . '分配至' . $systemUser['name'] . '，操作人' . $user['name'];
                        Db::name('AllotRecord')->insert($s_data);
                    }

                } else {

                }
                //$customers=Db::query("select * from crm_customer where (id in ($id_s) and id not in ($not_id)) and (user_id <>$user_id or user_id is null) limit 1");
                // $customer=$customers[0];
                //$customer=Db::name('CrmCustomer')->where('id','in',$ids)->where('id','not in',$not_id)->whereOr('user_id','<>',$user_id)->whereOr('user_id')->find();

                $i++;
            }


            $datetime = date('Y-m-d H:i:s', time());
            $notice_user_data = [];
            $notice_user_data['user_id'] = $user_id;
            $notice_user_data['notice_id'] = '';
            $notice_user_data['title'] = '您有新的消息';
            $notice_user_data['content'] = '你有新的客户数据导入';
            $notice_user_data['url'] = '/customer/customer/index';
            $notice_user_data['notice_time'] = $datetime;
            $notice_user_data['status'] = 0;
            $notice_user_data['type'] = 2;
            Db::name('CrmNoticeUser')->insert($notice_user_data);
            $this->success('modal|分配成功' . $cg . '条数据!' . $sb . '条数据因跨分公司分配失败!', '');
        } else {
            $this->error('请选择需要操作的数据');
        }
        // 提交事务

    }


    //随机分配-公共池用
    public function random_allot1()
    {

        $db = Db::name('SystemUser');
        $request = Request::instance();
        $ids = explode(',', $request->post('id', ''));
        $user = session('user');
        $department_id = $user['department_id'];
        if (!empty($ids)) {
            $data = $request->get();
            if (isset($data['department_id']) && $data['department_id'] !== "") {
                $department_list = Cache::store('redis')->get('department_tree' . $data['department_id']);
                $admin_user_list = $db->where('department_id', 'in', $department_list)->where('is_deleted', 0)->select();
            } else {
                $admin_user_list = Cache::store('redis')->get('department_user' . $department_id);
            }
            $department_list = Cache::store('redis')->get('operation_departemnt' . $user['id']);
            $department_list = ToolsService::arr2table($department_list);
            $this->assign('department_list', $department_list);

            return $this->fetch('random_allot1', ['admin_user_list' => $admin_user_list]);

        } else {
            $this->error('请选择需要操作的数据');
        }


    }

    //公共池随机分配
    public function randomAllotTo1($user_ids = '', $ids = '')
    {
        $last_time = date('Y-m-d H:i:s', strtotime("-7 day"));
        $allot_time = date('Y-m-d H:i:s', time());
        $user = session('user');
        // 启动事务
        $uuuu = $user['department_id'];
        if ($ids != '' && $user_ids != '') {
            $user_list = explode(',', $user_ids);
            $ids = explode(',', $ids);
            $cg = 0;
            $sb = 0;
            $i = 0;
            $user_count = count($user_list);
            $not_id = '';
            Db::startTrans();
            try {
                foreach ($ids as $id) {
                    $user_id = $user_list[$i % $user_count];
                    $customer_id = $id;
                    $systemUser = Db::name("SystemUser")->find($user_id);
                    $department_id = $systemUser['department_id'];
                    $customer = Db::name("CrmCustomer")->find($customer_id);
                    if ($uuuu == 1) {
                        $user_department_id = $systemUser['company_id'];
                        $customer_department_id = $this->getParentId($customer['department_id']);
                        if ($user_department_id == $customer_department_id || $customer['department_id'] == null) {

                            $s_data['type'] = 3;
                            $s_data['source_id'] = 0;
                            //$source=Db::name("SystemUser")->find($s_data['source_id']);
                            $s_data['user_id'] = $user_id;
                            $s_data['operate_id'] = $user['id'];
                            $s_data['department_id'] = $department_id;
                            $s_data['customer_id'] = $id;
                            $s_data['content'] = '数据由公共池分配至' . $systemUser['name'] . '，操作人' . $user['name'];
                            Db::name('AllotRecord')->insert($s_data);

                            $cg = $cg + 1;
                            $customer_model['user_id'] = $user_id;
                            $customer_model['allot_time'] = $allot_time;
                            $customer_model['last_time'] = $last_time;
                            $customer_model['redistribution_time'] = $allot_time;
                            $customer_model['department_id'] = $department_id;
                            $customer_model['new_data'] = 1;
                            $customer_model['is_reassign'] = 1;
                            $customer_model['is_public'] = null;
                            $customer_model['is_quit'] = null;
                            $customer_model['is_rubbish'] = null;
                            Db::name('CrmCustomer')->where('id', $customer_id)->update($customer_model);

                            $not_id = $not_id . ',' . $customer_id;
                        } else {
                            $sb = $sb + 1;

                            $not_id = $not_id . ',' . $customer_id;
                        }

                    } else {

                        $s_data['type'] = 3;
                        $s_data['source_id'] = $customer['user_id'];
                        //$source=Db::name("SystemUser")->find($s_data['source_id']);
                        $s_data['user_id'] = $user_id;
                        $s_data['operate_id'] = $user['id'];
                        $s_data['customer_id'] = $id;
                        $s_data['content'] = '数据由公共池分配至' . $systemUser['name'] . '，操作人' . $user['name'];
                        Db::name('AllotRecord')->insert($s_data);


                        $cg = $cg + 1;
                        $customer_model['user_id'] = $user_id;
                        $customer_model['allot_time'] = $allot_time;
                        $customer_model['last_time'] = $last_time;
                        $customer_model['redistribution_time'] = $allot_time;

                        $customer_model['department_id'] = $department_id;
                        $customer_model['new_data'] = 1;
                        $customer_model['is_reassign'] = 1;
                        $customer_model['is_public'] = null;
                        $customer_model['is_quit'] = null;
                        $customer_model['is_rubbish'] = null;
                        Db::name('CrmCustomer')->where('id', $customer_id)->update($customer_model);


                    }


                    $i++;

                }


                Db::commit();
            } catch (\Exception $e) {

                // 回滚事务
                Db::rollback();
                $this->error($e);
            }

            $datetime = date('Y-m-d H:i:s', time());
            $notice_user_data = [];
            $notice_user_data['user_id'] = $user_id;
            $notice_user_data['notice_id'] = '';
            $notice_user_data['title'] = '您有新的消息';
            $notice_user_data['content'] = '你有新的客户数据导入';
            $notice_user_data['url'] = '/customer/customer/index';
            $notice_user_data['notice_time'] = $datetime;
            $notice_user_data['status'] = 0;
            $notice_user_data['type'] = 2;
            Db::name('CrmNoticeUser')->insert($notice_user_data);
            $this->success('modal|分配成功' . $cg . '条数据!' . $sb . '条数据因跨分公司分配失败!', '');
        } else {
            $this->error('请选择需要操作的数据');
        }
        // 提交事务


    }

    //原始数据分配
    public function allot2()
    {
        $user = session('user');
        $request = Request::instance();
        $ids = explode(',', $request->post('id', ''));
        $allot_time = date('Y-m-d H:i:s', time());


        if (!empty($ids)) {

            $db = Db::name('SystemUser');
            $get = $this->request->get();
            $department_id = $user['department_id'];
            if ($department_id != 1) {
                $department_list = Cache::store('redis')->get('department_tree' . $user['department_id']);
                $comma_separated = implode(",", $department_list);
                $list3 = Db::query("SELECT user_id,count(*) as num from  (select * from allot_record where type=1) statistics where DATE_FORMAT(create_time,'%Y-%m-%d')=DATE_FORMAT('$allot_time','%Y-%m-%d') and statistics.user_id in (SELECT id from system_user where department_id in ($comma_separated))  GROUP BY user_id");
                $list4 = Db::query("SELECT count(*) as num from  (select * from allot_record where type=1) statistics where DATE_FORMAT(create_time,'%Y-%m-%d')=DATE_FORMAT('$allot_time','%Y-%m-%d') and statistics.user_id in (SELECT id from system_user where department_id in ($comma_separated))");
                $department_list = Cache::store('redis')->get('department_tree' . $department_id);
                $admin_user_list = $db->where('department_id', 'in', $department_list)->where('is_deleted', 0)->select();
            } else {
                $list3 = Db::query("SELECT user_id,count(*) as num from  (select * from allot_record where type=1) statistics where DATE_FORMAT(create_time,'%Y-%m-%d')=DATE_FORMAT('$allot_time','%Y-%m-%d') GROUP BY user_id");
                $list4 = Db::query("SELECT count(*) as num from  (select * from allot_record where type=1) statistics where DATE_FORMAT(create_time,'%Y-%m-%d')=DATE_FORMAT('$allot_time','%Y-%m-%d')");
                $admin_user_list = $db->where('is_deleted', 0)->select();
            }
            if (isset($get['department_id']) && $get['department_id'] !== "") {
                $department_list = Cache::store('redis')->get('department_tree' . $get['department_id']);
                $admin_user_list = $db->where('department_id', 'in', $department_list)->where('is_deleted', 0)->select();
            }
            $department_list = Cache::store('redis')->get('operation_departemnt' . $user['id']);
            $department_list = ToolsService::arr2table($department_list);
            $this->assign('department_list', $department_list);
            $this->assign('list4', $list4);
            $this->assign('lists', $list3);
            return $this->fetch('allot2', ['admin_user_list' => $admin_user_list, 'department_list' => $department_list]);


        } else {
            $this->error('请选择需要操作的数据');
        }

    }

    //原始数据分配-》数据分配
    public function allotTo2($user_id = 0, $ids = '', $shu = '')
    {
        $user = session("user");
        $allot_time = date('Y-m-d H:i:s', time());
        $date = date("Y-m-d");
        if ($ids != '' && $user_id != 0) {
            $list = explode(',', $ids);
            $cg = 0;
            $sb = 0;
            $systemUser = Db::name("SystemUser")->find($user_id);
            $quantity = $systemUser['quantity'];
            $department = Db::name("Department")->find($systemUser['company_id']);
            $balance = $department['balance'];


            $list3 = Db::query("SELECT * from  (select * from allot_record where type=1) statistics where user_id=$user_id and DATE_FORMAT(create_time,'%Y-%m-%d')=DATE_FORMAT('$allot_time','%Y-%m-%d')");

            if ((int)$shu > $quantity) {
                $this->error('数量过多');
            } else {
                if ($quantity - count($list3) >= (int)$shu) {
                    foreach ($list as $value) {


                        $customer = Db::name("CrmCustomer")->find($value);
                        if ($customer['status'] == 9) {
                            $file = Db::name("CrmFile")->find($customer['file_id']);

                            $price = $file['price'];


                            Db::startTrans();
                            try {
                                $s_data['type'] = 1;
                                $s_data['source_id'] = 0;
                                $s_data['user_id'] = $user_id;
                                $s_data['operate_id'] = $user['id'];
                                $s_data['customer_id'] = $customer['id'];
                                $s_data['file_id'] = $customer['file_id'];
                                $s_data['department_id'] = $systemUser['department_id'];
                                $s_data['content'] = '数据由数据中心' . $customer['city'] . '原始分配至' . $systemUser['name'] . ',操作人:' . $user['name'];
                                Db::name('AllotRecord')->insert($s_data);


                                //消费数据保存
                                $r_data['customer_id'] = $customer['id'];
                                $r_data['department_id'] = $systemUser['department_id'];
                                $r_data['user_id'] = $systemUser['id'];
                                $r_data['money'] = $price;
                                $r_data['customer_phone'] = $customer['mobile'];
                                $r_data['customer_name'] = $customer['name'];
                                Db::name('CrmConsumeRecord')->insert($r_data);


                                $customer_model['user_id'] = $user_id;
                                $customer_model['allot_time'] = $allot_time;
                                $customer_model['distribution_time'] = $allot_time;
                                $customer_model['status'] = 0;
                                $customer_model['new_data'] = 1;
                                $customer_model['department_id'] = $systemUser['department_id'];
                                $customer_model['remark_time'] = $allot_time;
                                Db::name('CrmCustomer')->where('id', $value)->update($customer_model);
                                Db::table('department')->where('id', $systemUser['company_id'])->setDec('balance', $price);
                                Db::table('data_user')->where('date', $date)->where('user_id', $user_id)->setInc('actual_quantity', 1);

                                Db::commit();

                                // $mobile=$customer['mobile'];
                                // $sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL
                                // $name=$user['name'];
                                // $num=$user['phone'];
                                // $str=$name.'-'.$num;
                                // $smsConf = array(
                                //     'key'   => '15f898218394e433dbc7592ecb3a3230', //您申请的APPKEY
                                //     'mobile'    => $mobile, //接受短信的用户手机号码
                                //     'tpl_id'    => '173833', //您申请的短信模板ID，根据实际情况修改
                                //     'tpl_value' =>'#name#='.$str//您设置的模板变量，根据实际情况修改
                                // );
                                // $content = $this->juhecurl($sendUrl,$smsConf,1); //请求发送短信

                                // //修改可分配数据用户已分配数据量

                                // //发送短信给客服
                                // $xing = substr($customer['mobile'],3,4);  //获取手机号中间四位
                                // $m_str =    str_replace($xing,'****',$customer['mobile']);
                                // $str=$customer['name'].'-'.$m_str;
                                // $smsConf2 = array(
                                //     'key'   => '15f898218394e433dbc7592ecb3a3230', //您申请的APPKEY
                                //     'mobile'    => $user['phone'], //接受短信的用户手机号码
                                //     'tpl_id'    => '174985', //您申请的短信模板ID，根据实际情况修改
                                //     'tpl_value' =>'#name#='.$str//您设置的模板变量，根据实际情况修改
                                // );
                                // $content = $this->juhecurl($sendUrl,$smsConf2,1); //请求发送短信


                            } catch (\Exception $e) {
                                Log::record($e->getMessage());
                                Db::rollback();
                                $this->success('modal|分配失败', '');
                            }
                        } elseif ($customer['status'] == 19) {
                            $customer_model['user_id'] = $user_id;
                            $customer_model['allot_time'] = $allot_time;
                            $customer_model['redistribution_time'] = $allot_time;
                            $customer_model['status'] = 19;
                            $customer_model['new_data'] = 1;
                            $customer_model['department_id'] = $systemUser['department_id'];
                            $customer_model['remark_time'] = $allot_time;
                            Db::name('CrmCustomer')->where('id', $value)->update($customer_model);
                        }


                    }


                    if ($cg == 0) {

                    } else {
                        $datetime = date('Y-m-d H:i:s', time());
                        $notice_user_data = [];
                        $notice_user_data['user_id'] = $user_id;
                        $notice_user_data['notice_id'] = '';
                        $notice_user_data['title'] = '您有新的消息';
                        $notice_user_data['content'] = '你有新的客户数据导入';
                        $notice_user_data['url'] = '/customer/customer/index';
                        $notice_user_data['notice_time'] = $datetime;
                        $notice_user_data['status'] = 0;
                        $notice_user_data['type'] = 2;
                        Db::name('CrmNoticeUser')->insert($notice_user_data);
                    }

                    $this->success('modal|分配成功', '');
                } else {
                    $this->error('数量过多');
                }


            }

        } else {
            $this->error('请选择需要操作的数据');
        }

    }

    //原始数据分配-》数据分配
    public function allotTo3($user_id = 0, $ids = '', $shu = '')
    {
        $user = session("user");
        $allot_time = date('Y-m-d H:i:s', time());
        $date = date("Y-m-d");
        if ($ids != '' && $user_id != 0) {
            $list = explode(',', $ids);
            $cg = 0;
            $sb = 0;
            $systemUser = Db::name("SystemUser")->find($user_id);
            $department = Db::name("Department")->find($systemUser['company_id']);
            $balance = $department['balance'];
            /*
            if($balance<(int)$shu*55){
                $this->error('余额不足');
            }*/
            foreach ($list as $value) {

                $customer = Db::name("CrmCustomer")->find($value);
                if ($customer['status'] == 9) {
                    $file = Db::name("CrmFile")->find($customer['file_id']);

                    $price = $file['price'];

                    Db::startTrans();
                    try {

                        $s_data['type'] = 1;
                        $s_data['source_id'] = 0;
                        $s_data['user_id'] = $user_id;
                        $s_data['operate_id'] = $user['id'];
                        $s_data['customer_id'] = $customer['id'];
                        $s_data['file_id'] = $customer['file_id'];
                        $s_data['department_id'] = $systemUser['department_id'];
                        $s_data['content'] = '数据由数据中心' . $customer['city'] . '原始分配至' . $systemUser['name'] . ',操作人:' . $user['name'];
                        Db::name('AllotRecord')->insert($s_data);

                        //消费数据保存
                        $r_data['customer_id'] = $customer['id'];
                        $r_data['department_id'] = $systemUser['department_id'];
                        $r_data['user_id'] = $systemUser['id'];
                        $r_data['money'] = $price;
                        $r_data['customer_phone'] = $customer['mobile'];
                        $r_data['customer_name'] = $customer['name'];
                        Db::name('CrmConsumeRecord')->insert($r_data);

                        $customer_model['user_id'] = $user_id;
                        $customer_model['allot_time'] = $allot_time;
                        $customer_model['distribution_time'] = $allot_time;
                        $customer_model['status'] = 0;
                        $customer_model['new_data'] = 1;
                        $customer_model['department_id'] = $systemUser['department_id'];
                        $customer_model['remark_time'] = $allot_time;
                        Db::name('CrmCustomer')->where('id', $value)->update($customer_model);
                        Db::table('department')->where('id', $systemUser['company_id'])->setDec('balance', $price);
                        Db::table('data_user')->where('date', $date)->where('user_id', $user_id)->setInc('actual_quantity', 1);
                        Db::commit();

                        //发送短信给客户
                        $mobile = $customer['mobile'];
                        $sendUrl = 'http://v.juhe.cn/sms/send'; //短信接口的URL
                        $name = $systemUser['name'];
                        $num = $systemUser['phone'];
                        $str = $name . '-' . $num;
                        $smsConf = array(
                            'key' => '15f898218394e433dbc7592ecb3a3230', //您申请的APPKEY
                            'mobile' => $mobile, //接受短信的用户手机号码
                            'tpl_id' => '190807', //您申请的短信模板ID，根据实际情况修改
                            'tpl_value' => '#name#=' . $str//您设置的模板变量，根据实际情况修改
                        );
                        $content = $this->juhecurl($sendUrl, $smsConf, 1); //请求发送短信

                        //修改可分配数据用户已分配数据量

                        //发送短信给业务员
                        $xing = substr($customer['mobile'], 3, 4);  //获取手机号中间四位
                        $m_str = str_replace($xing, '****', $customer['mobile']);
                        $str = $customer['name'] . '-' . $m_str;
                        $smsConf2 = array(
                            'key' => '15f898218394e433dbc7592ecb3a3230', //您申请的APPKEY
                            'mobile' => $systemUser['phone'], //接受短信的用户手机号码
                            'tpl_id' => '174985', //您申请的短信模板ID，根据实际情况修改
                            'tpl_value' => '#name#=' . $str//您设置的模板变量，根据实际情况修改
                        );
                        $content = $this->juhecurl($sendUrl, $smsConf2, 1); //请求发送短信


                    } catch (\Exception $e) {
                        Log::record($e->getMessage());
                        Db::rollback();
                        $this->success('分配失败', '');
                    }
                } elseif ($customer['status'] == 19) {
                    $customer_model['user_id'] = $user_id;
                    $customer_model['allot_time'] = $allot_time;
                    $customer_model['redistribution_time'] = $allot_time;
                    $customer_model['status'] = 19;
                    $customer_model['new_data'] = 1;
                    $customer_model['department_id'] = $systemUser['department_id'];
                    $customer_model['remark_time'] = $allot_time;
                    Db::name('CrmCustomer')->where('id', $value)->update($customer_model);
                }


            }

            if ($cg == 0) {

            } else {
                $datetime = date('Y-m-d H:i:s', time());
                $notice_user_data = [];
                $notice_user_data['user_id'] = $user_id;
                $notice_user_data['notice_id'] = '';
                $notice_user_data['title'] = '您有新的消息';
                $notice_user_data['content'] = '你有新的客户数据导入';
                $notice_user_data['url'] = '/customer/customer/index';
                $notice_user_data['notice_time'] = $datetime;
                $notice_user_data['status'] = 0;
                $notice_user_data['type'] = 2;
                Db::name('CrmNoticeUser')->insert($notice_user_data);
            }

            $this->success('分配成功', '');


        } else {
            $this->error('请选择需要操作的数据');
        }

    }

    //原始数据分配-》部门调换
    public function allotTo4($department_id = 0, $ids = '', $shu = '')
    {

        if ($ids != '' && $department_id != 0) {
            $list = explode(',', $ids);

            foreach ($list as $value) {
                Db::startTrans();
                try {
                    $customer = Db::name("CrmCustomer")->find($value);
                    $department = Db::name("Department")->find($department_id);

                    if ($customer['city'] == $department['city']) {
                        Db::name('CrmCustomer')->where('id', $value)->update(['department_id' => $department_id]);
                        Db::commit();
                    }
                } catch (\Exception $e) {
                    Log::record($e->getMessage());
                    Db::rollback();
                    $this->success('分配失败', '');
                }

            }
            $this->success('调度成功', '');

        } else {
            $this->error('请选择需要操作的数据');
        }

    }

    //公共池数据再分配
    public function public_allot()
    {
        $request = Request::instance();
        $ids = explode(',', $request->post('id', ''));
        $db = Db::name('SystemUser');
        $user = session('user');
        $department_id = $user['department_id'];
        if (!empty($ids)) {
            $data = $request->get();
            if (isset($data['department_id']) && $data['department_id'] !== "") {
                $department_list = Cache::store('redis')->get('department_tree' . $data['department_id']);
                $admin_user_list = $db->where('department_id', 'in', $department_list)->where('is_deleted', 0)->select();
            } else {
                $admin_user_list = Cache::store('redis')->get('department_user' . $department_id);
            }
            $department_list = Cache::store('redis')->get('operation_departemnt' . $user['id']);
            $department_list = ToolsService::arr2table($department_list);
            $this->assign('department_list', $department_list);

            return $this->fetch('public_allot', ['admin_user_list' => $admin_user_list]);

        } else {
            $this->error('请选择需要操作的数据');
        }


    }

    //公共池数据再分配-数据分配
    public function publicAllotTo($user_id = 0, $ids = '')
    {
        $last_time = date('Y-m-d H:i:s', strtotime("-7 day"));
        $allot_time = date('Y-m-d H:i:s', time());
        $user = session('user');
        if ($ids != '') {
            $list = explode(',', $ids);
            $cg = 0;
            $sb = 0;
            $systemUser = Db::name("SystemUser")->find($user_id);
            $user_department_id = $systemUser['department_id'];

            //判断操作人是不是系统管理员
            if ($user['department_id'] == 1) {

                foreach ($list as $value) {
                    if ($value == "") {
                        $this->error('请刷新后再试');
                    } else {
                        $customer = Db::name("CrmCustomer")->find($value);
                        if ($user_department_id == 1) {
                            $cg = $cg + 1;

                            $s_data['type'] = 3;
                            $s_data['source_id'] = 0;
                            $s_data['user_id'] = $user_id;
                            $s_data['operate_id'] = $user['id'];
                            $s_data['customer_id'] = $customer['id'];
                            $s_data['department_id'] = $systemUser['department_id'];
                            $s_data['content'] = '数据由公共池再分配至' . $systemUser['name'] . ',操作人:' . $user['name'];
                            Db::name('AllotRecord')->insert($s_data);

                            $customer_model['user_id'] = $user_id;
                            $customer_model['allot_time'] = $allot_time;
                            $customer_model['last_time'] = $last_time;
                            $customer_model['is_public'] = null;
                            $customer_model['is_quit'] = null;
                            $customer_model['is_rubbish'] = null;
                            $customer_model['redistribution_time'] = $allot_time;

                            $customer_model['department_id'] = $user_department_id;
                            $customer_model['new_data'] = 1;
                            $customer_model['is_reassign'] = 1;
                            $customer_model['remark_time'] = $allot_time;
                            Db::name('CrmCustomer')->where('id', $value)->update($customer_model);


                        } else {
                            $user_department_id = $systemUser['company_id'];
                            $customer_department_id = $this->getParentId($customer['department_id']);

                            if ($user_department_id == $customer_department_id || $customer['department_id'] == null) {
                                $s_data['type'] = 3;
                                $s_data['source_id'] = 0;
                                $s_data['user_id'] = $user_id;
                                $s_data['operate_id'] = $user['id'];
                                $s_data['customer_id'] = $customer['id'];;
                                $s_data['department_id'] = $systemUser['department_id'];
                                $s_data['content'] = '数据由公共池再分配至' . $systemUser['name'] . ',操作人:' . $user['name'];
                                Db::name('AllotRecord')->insert($s_data);


                                $cg = $cg + 1;
                                $customer_model['user_id'] = $user_id;
                                $customer_model['allot_time'] = $allot_time;
                                $customer_model['last_time'] = $last_time;
                                $customer_model['is_public'] = null;
                                $customer_model['is_quit'] = null;
                                $customer_model['is_rubbish'] = null;
                                $customer_model['redistribution_time'] = $allot_time;
                                $customer_model['department_id'] = $user_department_id;
                                $customer_model['new_data'] = 1;
                                $customer_model['is_reassign'] = 1;
                                $customer_model['remark_time'] = $allot_time;
                                Db::name('CrmCustomer')->where('id', $value)->update($customer_model);

                            } else {
                                $sb = $sb + 1;

                            }
                        }
                    }
                }

                if ($cg == 0) {

                } else {
                    $datetime = date('Y-m-d H:i:s', time());
                    $notice_user_data = [];
                    $notice_user_data['user_id'] = $user_id;
                    $notice_user_data['notice_id'] = '';
                    $notice_user_data['title'] = '您有新的消息';
                    $notice_user_data['content'] = '你有新的客户数据导入';
                    $notice_user_data['url'] = '/customer/customer/index';
                    $notice_user_data['notice_time'] = $datetime;
                    $notice_user_data['status'] = 0;
                    $notice_user_data['type'] = 2;
                    Db::name('CrmNoticeUser')->insert($notice_user_data);
                }

                $this->success('modal|分配成功' . $cg . '条数据!' . $sb . '条数据因跨分公司分配失败!', '');
            } else {
                foreach ($list as $value) {
                    $customer = Db::name("CrmCustomer")->find($value);
                    $s_data['type'] = 3;
                    $s_data['source_id'] = 0;
                    $s_data['user_id'] = $user_id;
                    $s_data['operate_id'] = $user['id'];
                    $s_data['customer_id'] = $customer['id'];
                    $s_data['department_id'] = $systemUser['department_id'];
                    $s_data['content'] = '数据由公共池再分配至' . $systemUser['name'] . ',操作人:' . $user['name'];
                    Db::name('AllotRecord')->insert($s_data);


                    $customer_model['user_id'] = $user_id;
                    $customer_model['allot_time'] = $allot_time;
                    $customer_model['last_time'] = $last_time;
                    $customer_model['is_public'] = null;
                    $customer_model['is_quit'] = null;
                    $customer_model['is_rubbish'] = null;
                    $customer_model['redistribution_time'] = $allot_time;

                    $customer_model['department_id'] = $user_department_id;
                    $customer_model['new_data'] = 1;
                    $customer_model['is_reassign'] = 1;
                    $customer_model['remark_time'] = $allot_time;
                    Db::name('CrmCustomer')->where('id', $value)->update($customer_model);


                }


                $datetime = date('Y-m-d H:i:s', time());
                $notice_user_data = [];
                $notice_user_data['user_id'] = $user_id;
                $notice_user_data['notice_id'] = '';
                $notice_user_data['title'] = '您有新的消息';
                $notice_user_data['content'] = '你有新的客户数据导入';
                $notice_user_data['url'] = '/customer/customer/index';
                $notice_user_data['notice_time'] = $datetime;
                $notice_user_data['status'] = 0;
                $notice_user_data['type'] = 2;
                Db::name('CrmNoticeUser')->insert($notice_user_data);

                $this->success('modal|分配成功', '');
            }

        } else {
            $this->error('请选择需要操作的数据');
        }

    }

    //团队客户数据再分配
    public function allot()
    {
        $request = Request::instance();
        $ids = explode(',', $request->post('id', ''));
        $db = Db::name('SystemUser');
        $user = session('user');
        $department_id = $user['department_id'];
        if (!empty($ids)) {
            $data = $request->get();
            if (isset($data['department_id']) && $data['department_id'] !== "") {
                $department_list = Cache::store('redis')->get('department_tree' . $data['department_id']);
                $admin_user_list = $db->where('department_id', 'in', $department_list)->where('is_deleted', 0)->select();
            } else {
                $admin_user_list = Cache::store('redis')->get('department_user' . $department_id);
            }
            $department_list = Cache::store('redis')->get('operation_departemnt' . $user['id']);
            $department_list = ToolsService::arr2table($department_list);
            $this->assign('department_list', $department_list);

            return $this->fetch('allot', ['admin_user_list' => $admin_user_list]);

        } else {
            $this->error('请选择需要操作的数据');
        }

    }

    //团队客户数据再分配-数据分配
    public function allotTo($user_id = 0, $ids = '')
    {
        $last_time = date('Y-m-d H:i:s', time());
        $allot_time = date('Y-m-d H:i:s', time());
        $user = session('user');
        if ($ids != '') {

            $list = explode(',', $ids);
            $cg = 0;
            $sb = 0;
            //判断分配目标是不是最高权限
            $systemUser = Db::name("SystemUser")->find($user_id);
            $user_department_id = $systemUser['department_id'];

            //判断操作人是不是系统管理员
            if ($user['department_id'] == 1) {

                foreach ($list as $value) {
                    if ($value == "") {
                        $this->error('请刷新后再试');
                    } else {
                        $customer = Db::name("CrmCustomer")->find($value);

                        if ($user_department_id == 1) {
                            $cg = $cg + 1;

                            $s_data['type'] = 2;
                            $s_data['source_id'] = $customer['user_id'];
                            $source = Db::name("SystemUser")->find($s_data['source_id']);
                            $s_data['user_id'] = $user_id;
                            $s_data['operate_id'] = $user['id'];
                            $s_data['customer_id'] = $customer['id'];
                            $s_data['department_id'] = $systemUser['department_id'];
                            $s_data['content'] = '数据由' . $source['name'] . '再分配至' . $systemUser['name'] . ',操作人:' . $user['name'];
                            Db::name('AllotRecord')->insert($s_data);

                            $customer_model['user_id'] = $user_id;
                            $customer_model['allot_time'] = $allot_time;
                            $customer_model['last_time'] = $last_time;
                            $customer_model['is_public'] = null;
                            $customer_model['is_quit'] = null;
                            $customer_model['is_rubbish'] = null;
                            $customer_model['redistribution_time'] = $allot_time;

                            $customer_model['department_id'] = $user_department_id;
                            $customer_model['new_data'] = 1;
                            $customer_model['is_reassign'] = 1;
                            $customer_model['remark_time'] = $allot_time;
                            Db::name('CrmCustomer')->where('id', $value)->update($customer_model);


                        } else {

                            $user_department_id = $systemUser['company_id'];
                            $customer_department_id = $this->getParentId($customer['department_id']);


                            if ($user_department_id == $customer_department_id || $customer['department_id'] == null) {
                                $cg = $cg + 1;
                                $s_data['type'] = 2;
                                $s_data['source_id'] = $customer['user_id'];
                                $source = Db::name("SystemUser")->find($s_data['source_id']);
                                $s_data['user_id'] = $user_id;
                                $s_data['operate_id'] = $user['id'];
                                $s_data['customer_id'] = $customer['id'];
                                $s_data['department_id'] = $systemUser['department_id'];
                                $s_data['content'] = '数据由' . $source['name'] . '再分配至' . $systemUser['name'] . ',操作人:' . $user['name'];
                                Db::name('AllotRecord')->insert($s_data);

                                $customer_model['user_id'] = $user_id;
                                $customer_model['allot_time'] = $allot_time;
                                $customer_model['last_time'] = $last_time;
                                $customer_model['is_public'] = null;
                                $customer_model['is_quit'] = null;
                                $customer_model['is_rubbish'] = null;
                                $customer_model['redistribution_time'] = $allot_time;
                                $customer_model['department_id'] = $user_department_id;
                                $customer_model['new_data'] = 1;
                                $customer_model['is_reassign'] = 1;
                                $customer_model['remark_time'] = $allot_time;
                                Db::name('CrmCustomer')->where('id', $value)->update($customer_model);

                            } else {
                                $sb = $sb + 1;

                            }
                        }
                    }
                }

                if ($cg == 0) {

                } else {
                    $datetime = date('Y-m-d H:i:s', time());
                    $notice_user_data = [];
                    $notice_user_data['user_id'] = $user_id;
                    $notice_user_data['notice_id'] = '';
                    $notice_user_data['title'] = '您有新的消息';
                    $notice_user_data['content'] = '你有新的客户数据导入';
                    $notice_user_data['url'] = '/customer/customer/index';
                    $notice_user_data['notice_time'] = $datetime;
                    $notice_user_data['status'] = 0;
                    $notice_user_data['type'] = 2;
                    Db::name('CrmNoticeUser')->insert($notice_user_data);
                }

                $this->success('modal|分配成功' . $cg . '条数据!' . $sb . '条数据因跨分公司分配失败!', '');
            } else {
                foreach ($list as $value) {
                    $customer = Db::name("CrmCustomer")->find($value);

                    $s_data['type'] = 2;
                    $s_data['source_id'] = $customer['user_id'];
                    $source = Db::name("SystemUser")->find($s_data['source_id']);
                    $s_data['user_id'] = $user_id;
                    $s_data['operate_id'] = $user['id'];
                    $s_data['customer_id'] = $customer['id'];
                    $s_data['department_id'] = $systemUser['department_id'];
                    $s_data['content'] = '数据由' . $source['name'] . '再分配至' . $systemUser['name'] . ',操作人:' . $user['name'];
                    Db::name('AllotRecord')->insert($s_data);

                    $customer_model['user_id'] = $user_id;
                    $customer_model['allot_time'] = $allot_time;
                    $customer_model['last_time'] = $last_time;
                    $customer_model['is_public'] = null;
                    $customer_model['is_quit'] = null;
                    $customer_model['is_rubbish'] = null;
                    $customer_model['redistribution_time'] = $allot_time;
                    $customer_model['department_id'] = $user_department_id;
                    $customer_model['new_data'] = 1;
                    $customer_model['is_reassign'] = 1;
                    $customer_model['remark_time'] = $allot_time;
                    Db::name('CrmCustomer')->where('id', $value)->update($customer_model);


                }


                $datetime = date('Y-m-d H:i:s', time());
                $notice_user_data = [];
                $notice_user_data['user_id'] = $user_id;
                $notice_user_data['notice_id'] = '';
                $notice_user_data['title'] = '您有新的消息';
                $notice_user_data['content'] = '你有新的客户数据导入';
                $notice_user_data['url'] = '/customer/customer/index';
                $notice_user_data['notice_time'] = $datetime;
                $notice_user_data['status'] = 0;
                $notice_user_data['type'] = 2;
                Db::name('CrmNoticeUser')->insert($notice_user_data);

                $this->success('modal|分配成功', '');
            }

        } else {
            $this->error('请选择需要操作的数据');
        }

    }

    public function department_list()
    {

        $user = session('user');
        $request = Request::instance();
        $ids = explode(',', $request->post('id', ''));
        $allot_time = date('Y-m-d H:i:s', time());
        $get = $this->request->get();
        $department_list = Cache::store('redis')->get('department_tree' . $get['department_id']);
        $comma_separated = implode(",", $department_list);
        $admin_user_list = Db::query("SELECT system_user.id,system_user.name,system_user.quantity,user_allot.is_new as is_receive  ,department.name as department_name,t1.num as num from system_user LEFT JOIN department ON system_user.department_id=department.id  LEFT JOIN (SELECT user_id,count(*) as num from  (select * from allot_record where type=1) statistics where DATE_FORMAT(create_time,'%Y-%m-%d')=DATE_FORMAT('$allot_time','%Y-%m-%d') GROUP BY user_id  ) as t1  on system_user.id=t1.user_id left join user_allot on user_allot.user_id=system_user.id  where system_user.is_deleted=0 and system_user.department_id IN ($comma_separated)   GROUP BY system_user.id");


        return json($admin_user_list);
    }


    /**
     * 请求接口返回内容
     * @param string $url [请求的URL地址]
     * @param string $params [请求的参数]
     * @param int $ipost [是否采用POST形式]
     * @return  string
     */
    function juhecurl($url, $params = false, $ispost = 0)
    {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        $response =  'AZ');


        for ($i = 0; $i < $cellNum; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . '1', $expCellName[$i][1]);
        }
        // Miscellaneous glyphs, UTF-8

        for ($i = 0; $i < $dataNum; $i++) {
            for ($j = 0; $j < $cellNum; $j++) {

                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 2), $expTableData[$i][$expCellName[$j][0]]);

            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    /**
     *
     * 导出Excel
     */

    function export1($ids = '')
    {//导出Excel

        if ($ids != '') {
            $xlsName = date("Y/m/d i:m:s");
     curl_exec($ch);
        if ($response === FALSE) {
            //echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }

    public function exportExcel($expTitle, $expCellName, $expTableData)
    {

        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        vendor("PHPExcel.PHPExcel");

        $objPHPExcel = new PHPExcel();
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY',       $xlsCell = array(
                array('id', 'ID'),
                array('name', '姓名'),
                array('mobile', '联系方式'),
                array('sex', '性别'),
                array('status', '类型'),
                array('customer_type_id', '星级'),
                array('remarks', '资质'),
                array('money_demand', '申请金额(元)'),
                array('create_time', '申请时间'),


            );
            $xlsData = [];

            $xlsData = Db::query(" SELECT id,name,mobile,CASE sex WHEN 1 THEN '男' WHEN 2 THEN '女'  END as sex,CASE status WHEN 0 THEN '未受理' WHEN 1 THEN '待跟进' WHEN 2 THEN '已上门' WHEN 3 THEN '待签约'WHEN 4 THEN '已签单'WHEN 5 THEN '已放款'WHEN 6 THEN '再分配'WHEN 7 THEN '离职人员客户'WHEN 8 THEN '已提交'WHEN 9 THEN '未分配客户'WHEN 10 THEN '客户资质不符'WHEN 11 THEN '捣乱申请'WHEN 12 THEN '外地申请'WHEN 13 THEN '重复'WHEN 14 THEN '多次申请' ELSE '无' END as status,CASE customer_type_id   WHEN 1 THEN '一星' WHEN 2 THEN '二星' WHEN 3 THEN '三星'WHEN 4 THEN '四星'WHEN 5 THEN '重要客户'WHEN 6 THEN '0星' END as customer_type_id,remarks,money_demand,create_time from crm_customer where id in ($ids)");


            //$xlsData  = $xlsModel->Field('name','money_demand','place','mobile','phone','customer_type_id','status','credit_score','webank')->select();
            $this->exportExcel($xlsName, $xlsCell, $xlsData);


        } else {
            $this->error('请选择需要导出的数据');
        }


    }

    /**
     *
     * 导出Excel
     */
    function export()
    {//导出Excel
        $xlsName = "Customer";
        $xlsCell = array(
            array('name', '客户名称'),
            array('money_demand', '资金需求'),
            array('place', '户籍'),
            array('mobile', '手机号码'),
            array('phone', '联系电话'),
            array('customer_type_id', '客户类型'),
            array('status', '状态'),
            array('credit_score', '芝麻信用分'),
            array('webank', '微粒贷'),
            array('is_marry', '婚姻状态'),

        );
        $xlsData = [];
        $user = session('user');
        if ($user['department_id'] == '1') {
            $xlsData = DB::query("select crm_customer.name,money_demand,place,mobile,phone,crm_customer_type.name as customer_type_id,CASE status WHEN 1 THEN '跟进' WHEN 2 THEN '已上门' WHEN 3 THEN '待签约'WHEN 4 THEN '已签单'WHEN 5 THEN '已放款'WHEN 6 THEN '再分配' ELSE '无' END as status,credit_score,webank,if(is_marry=0,'是','否') as is_marry from crm_customer left join crm_customer_type on crm_customer.customer_type_id=crm_customer_type.id");

        } else {
            $department_id = $user['department_id'];
            $xlsData = DB::query("select crm_customer.name,money_demand,place,mobile,phone,crm_customer_type.name as customer_type_id,CASE status WHEN 1 THEN '跟进' WHEN 2 THEN '已上门' WHEN 3 THEN '待签约'WHEN 4 THEN '已签单'WHEN 5 THEN '已放款'WHEN 6 THEN '再分配' ELSE '无' END as status,credit_score,webank,if(is_marry=0,'是','否') as is_marry from crm_customer left join crm_customer_type on crm_customer.customer_type_id=crm_customer_type.id where crm_customer.department_id=$department_id");

        }
        //$xlsData  = $xlsModel->Field('name','money_demand','place','mobile','phone','customer_type_id','status','credit_score','webank')->select();
        $this->exportExcel($xlsName, $xlsCell, $xlsData);

    }

    public function sync()
    {
        $user = session('user');
        $list = $this->getDepartmentIds($user['department_id']);
        $department_list = Db::name("Department")->whereNull('is_deleted')->where('id', 'in', $list)->select();

        Cache::store('redis')->set('operation_departemnt' . $user['id'], $department_list);

        if ($user['department_id'] == 1) {
            $user_list = Db::name('SystemUser')->where('is_deleted', 0)->select();
        } else {
            $user_list = Db::name('SystemUser')->where('is_deleted', 0)->where('department_id', 'in', $list)->select();
        }
        Cache::store('redis')->set('operation_user' . $user['id'], $user_list);

    }

    //添加到垃圾库
    function setrubbish()
    {
        $request = Request::instance();
        $ids = explode(',', $request->post('id', ''));
        if ($ids != '') {
            $customer_model['is_rubbish'] = 1;
            $customer_model['user_id'] = null;
            $customer_model['is_public'] = null;
            $customer_model['is_quit'] = null;
            $customer_model['is_rubbish'] = 1;
            Db::name('CrmCustomer')->where('id', 'in', $ids)->update($customer_model);

            $this->success('modal|加入成功');
        } else {
            $this->error('modal|请选择需要操作的数据');
        }
        // 提交事务
    }

    public function importqweqwe()
    {
        vendor("PHPExcel.PHPExcel");

        $file = "C:\Users\Administrator.MICROSOFT\Downloads";

        if ($file) {


            $objReader = PHPExcel_IOFactory::createReader('Excel2007');

            $objPHPExcel = $objReader->load($file);

            $sheetDatas = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
            unset($sheetDatas[1]);

            foreach ($sheetDatas as $value) {
                if ($value['B'] == null) {

                } else {
                    $mobile = $value['B'];


                    //$m_count=Db::name("CrmCustomer")->where("mobile",$mobile)->count();
                    if ($mobile != '') {
                        $customer_model['name'] = $value['A'];
                        $customer_model['mobile'] = $value['B'];
                        $customer_model['remarks'] = $value['C'];
                        $customer_model['city'] = '南京市';
                        $customer_model['department_id'] = 145;
                        $customer_model['status'] = 0;
                        // $customer_model['new_data']= 1;
                        $customer_model['file_id'] = '';
                        $customer_model['create_id'] = 112711;
                        $customer_model['is_public'] = 1;
                        // $customer_model['redistribution_time']=date('Y-m-d H:i:s',time());
                        //$customer_model['distribution_time']=date('Y-m-d H:i:s',time());
                        // $customer_model['allot_time']=date('Y-m-d H:i:s',time());

                        Db::name('CrmCustomer')->insert($customer_model);
                    }


                }

            }
            $this->success('modal|恭喜, 数据保存成功!');

        }
    }

    function getParentId($id)
    {

        $department1 = Db::name('Department')->where('id', $id)->find();

        if ($department1['pid'] == 1) {
            return $department1['id'];
        } else {

            $department2 = Db::name('Department')->where('id', $department1['pid'])->find();
            if ($department2['pid'] == 1) {
                return $department2['id'];
            } else {

                $department3 = Db::name('Department')->where('id', $department2['pid'])->find();
                if ($department3['pid'] == 1) {
                    return $department3['id'];
                }
            }
        }
    }

    //添加到垃圾库
    function reset()
    {
        $request = Request::instance();
        $ids = explode(',', $request->post('id', ''));
        if ($ids != '') {
            $reset_time = date('Y-m-d H:i:s', time());
            $customer_model['reset_time'] = $reset_time;
            Db::name('CrmCustomer')->where('id', 'in', $ids)->update($customer_model);
            $this->success('modal|重置成功');
        } else {
            $this->error('modal|请选择需要操作的数据');
        }
        // 提交事务
    }

    /**
     * 充值记录
     */
    public function rechargerecord($id)
    {
        $company = Db::name('recharge_companys')->find($id);
        $this->title = '充值记录';
        $db = Db::name('recharge_records');
        $count = Db::table('recharge_records')->count();//有多少条数据
        $db->where('rc_id', $id);
        $page = $db->paginate(20, $count, ['query' => $this->request->get('', '', 'urlencode')]);
        list($pattern, $replacement) = [['|href="(.*?)"|', '|pagination|'], ['data-page="$1"', 'pagination pull-right']];
        list($result['list'], $result['page']) = [$page->all(), preg_replace($pattern, $replacement, $page->render())];
        !empty($this->title) && $this->assign('title', $this->title);
        $this->assign('company', $company);
        return $this->fetch('', $result);
    }

    /**
     * 充值
     * @param $id
     * @return mixed
     */
    public function recharge($id)
    {
        if ($this->request->isPost()) {
            Db::startTrans();
            try {
                $data = $this->request->param();
                $user = session('user');
                $notice_user_data['rc_id'] = $data['id'];
                $notice_user_data['money'] = $data['money'];
                $notice_user_data['create_time'] = date('Y-m-d H:i:s');
                $notice_user_data['admin_id'] = $user['id'];

                $res1 = Db::name('recharge_records')->insert($notice_user_data);
                $res2 = Db::table("recharge_companys")->where('id', $data['id'])->setInc('all_money', $data['money']);
                if (!$res1 || !$res2) {
                    // 回滚事务
                    Db::rollback();
                    $this->error('充值失败!', url('customer/customer/index99'));
                }
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                //var_dump($e->getMessage());
                // 回滚事务
                Db::rollback();
                $this->error('充值失败!', url('customer/customer/index99'));
            }
            $this->success('恭喜, 充值成功成功!', url('customer/customer/index99'));
        } else {
            return $this->fetch('recharge', ['id' => $id]);
        }
    }

    function set_sources_price()
    {
        $data = $this->request->param();
        $res2 = Db::table("recharge_companys")->where('id', $data['id'])->update(['sources_price' => $data['money']]);
        return true;
    }

    function set_company_total()
    {
        $data = $this->request->param();
        $res2 = Db::table("recharge_companys")->where('id', $data['id'])->update(['total' => $data['total']]);
        return true;
    }

    /**
     * 启用
     */
    public function resume()
    {
        $data = $this->request->param();
        $data_new['status'] = $data['value'];
        Db::name('RechargeCompanys')->where('id', $data['id'])->update($data_new);
        $this->success("modal|启用成功！", '');
        $this->error("modal|启用失败，请稍候再试！");
    }

    public function journal()
    {

        $this->title = '日志报表';
        $user = session('user');
        $department_list = Db::name('Department')->where('id', $user['department_id'])->select();
        $user_list = Db::name('SystemUser')->where('id', $user['id'])->select();
        $customer_list = Db::name('CrmCustomer')->where('user_id', $user['id'])->select();

        $this->assign('department_list', $department_list);
        $this->assign('user_list', $user_list);
        $this->assign('customer_list', $customer_list);
        $db = Db::name('crm_journal')->where('is_deleted', 0)->order('create_time desc');
        if ($user['authorize'] == 2) {
            $db->where('user_id', $user['id']);
        } elseif ($user['authorize'] == 8) {
            $department_list = Cache::store('redis')->get('department_tree' . $user['department_id']);
            $db->where('department_id', 'in', $department_list);
        } elseif ($user['authorize'] == 6) {
            $department_list = Cache::store('redis')->get('department_tree' . $user['department_id']);
            $db->where('department_id', 'in', $department_list);
        } elseif ($user['authorize'] == 9) {
            $department_list = Cache::store('redis')->get('department_tree' . $user['department_id']);
            $db->where('department_id', 'in', $department_list);
        }


        $arr = [];
        $arr[] = [
            'id' => 2,
            'name' => '已上门'
        ];
        $arr[] = [
            'id' => 3,
            'name' => '待签约'
        ];
        $arr[] = [
            'id' => 4,
            'name' => '已签约'
        ];
        $arr[] = [
            'id' => 5,
            'name' => '已放款'
        ];
        $arr[] = [
            'id' => 19,
            'name' => '已进件'
        ];
        $arr[] = [
            'id' => 16,
            'name' => '债务重组'
        ];
        $this->assign('status_list', $arr);


        return parent::_list($db);

    }

    public function addjournal()
    {

        if (!$this->request->isPost()) {
            $user = session('user');
            $user_id = $user['id'];
            $department_id = $user['department_id'];
            $arr = [];
            $arr[] = [
                'id' => 2,
                'name' => '已上门'
            ];
            $arr[] = [
                'id' => 3,
                'name' => '待签约'
            ];
            $arr[] = [
                'id' => 4,
                'name' => '已签约'
            ];
            $arr[] = [
                'id' => 5,
                'name' => '已放款'
            ];
            $arr[] = [
                'id' => 19,
                'name' => '已进件'
            ];
            $arr[] = [
                'id' => 16,
                'name' => '债务重组'
            ];
            $this->assign('status_list', $arr);
            $user_info = Db::name('SystemUser')->find($user_id);
            $user_list = Db::name('SystemUser')->where('status', 1)->where('is_deleted', 0)->select();
            $department_list = Db::name('Department')->find($department_id);
            return $this->fetch('addjournal', ['user_info' => $user_info, 'user_list' => $user_list, 'department' => $department_list]);
        } else {

            $data = $this->request->param();
            $user = session('user');
            $user_id = $user['id'];
            $department_id = $user['department_id'];
            $user_list = Db::name('SystemUser')->find($user_id);
            $department_list = Db::name('Department')->find($department_id);
            if (isset($data['customer_mobile']) && $data['customer_mobile'] != '') {
                $mobile = $data['customer_mobile'];
                $customer = Db::name('CrmCustomer')->where('mobile', $mobile)->find();
                if ($customer) {
                    // Db::name('CrmCustomer')->where('id',$customer['id'])->update(array('status'=>5));
                    $in_the_trial['customer_id'] = $customer['id'];
                } else {
                    $this->error('客户不存在!');
                }

            } else {
                $customer['name'] = '';
            }

            // $in_the_trial['file_id'] = $customer['file_id'];
            $in_the_trial['customer_name'] = $customer['name'];
            $in_the_trial['customer_mobile'] = $data['customer_mobile'];
            $in_the_trial['department_name'] = $department_list['name'];
            $in_the_trial['user_name'] = $user_list['name'];
            $in_the_trial['user_id'] = $user['id'];
            $in_the_trial['status'] = $data['status'];
            $in_the_trial['remarks'] = $data['remarks'];
            $in_the_trial['department_id'] = $user['department_id'];
            if ($data['customer_mobile'] != '' && $data['status'] != '') {
                $arr['status'] = $data['status'];
                Db::name('CrmCustomer')->where('mobile', $data['customer_mobile'])->where('user_id', $user['id'])->update($arr);
            }
            Db::name('SystemUser')->where('id', $user_id)->update(array('journal' => 1));

            $in_the_trial_id = Db::name('crm_journal')->insertGetId($in_the_trial);
            $this->success('恭喜, 数据保存成功!', '');
        }


    }

    public function editjou($id)
    {
        $detaile = Db::name('crm_journal')->where('id', $id)->find();
        $arr = [];
        $arr[] = [
            'id' => 2,
            'name' => '已上门'
        ];
        $arr[] = [
            'id' => 3,
            'name' => '待签约'
        ];
        $arr[] = [
            'id' => 4,
            'name' => '已签约'
        ];
        $arr[] = [
            'id' => 5,
            'name' => '已放款'
        ];
        $arr[] = [
            'id' => 19,
            'name' => '已进件'
        ];
        $arr[] = [
            'id' => 16,
            'name' => '债务重组'
        ];
        $this->assign('status_list', $arr);
        $this->assign('vo', $detaile);
        return $this->fetch('editjou');
    }

    public function index99()
    {
        $db2 = [
            // 数据库类型
            'type' => 'mysql',
            // 服务器地址
            'hostname' => '172.16.181.235',
            // 数据库名
            'database' => 'pjygj_bak831_new',
            // 用户名
            'username' => 'pjygj',
            // 密码
            'password' => 'mTyxsr4zXTtY2019$',
            // 端口
            'hostport' => '3306',
            // 数据库编码默认采用utf8
            'charset' => 'utf8',
            // 数据库表前缀
            'prefix' => '',
        ];
        $page_current = intval($this->request->get('page', 1));
        $companyname = $this->request->get('companyname');
        $status = $this->request->get('status');
        $create_date = $this->request->get('create_date');
        $rows = 20;
        $this->title = '公司充值管理';
        $db = Db::name('recharge_companys');
        //$count = Db::table('recharge_companys')->count();//有多少条数据
        $old_data = Db::table('recharge_companys');
        if ($companyname) {
            $old_data->where('name', 'like', "%$companyname%");
            $db->where('name', 'like', "%$companyname%");
        }
        if ($status) {
            $old_data->where('status', $status);
            $db->where('status', $status);
        }

        $old_data = $old_data->select(); //原始所有的数据
        $count = count($old_data);
        //$rows = intval($this->request->get('rows', cookie('rows')));
        //cookie('rows', $rows >= 10 ? $rows : 20);
        $db->where('name', 'like', "%$companyname%")->where('status', $status);
        $page = $db->paginate($rows, $count, ['query' => $this->request->get('', '', 'urlencode')]);
        list($pattern, $replacement) = [['|href="(.*?)"|', '|pagination|'], ['data-page="$1"', 'pagination pull-right']];
        list($result['list'], $result['page']) = [$page->all(), preg_replace($pattern, $replacement, $page->render())];
        //**************** 新系统的供给资源统计  *******************
        $res = Db::table('recharge_companys')->field('old_id')->where('type', 2)->select();
        foreach ($res as $k => $v) {
            $ids[] = $v['old_id'];
        }
        $ids = implode(',', $ids);
        // 新系统每个渠道分配了多少条数据
        if (!$create_date) {
            $sql = "select count(id) as c,file_id from crm_customer where status=9 and  file_id in($ids) and (create_id is null OR create_id=1578891093) and create_time > '2020-4-1 00:00:00' GROUP BY file_id";
        } else {
            list($start, $end) = explode('-', str_replace(' ', ' ', $create_date));
            $sql = "select count(id) as c,file_id from crm_customer where status=9 and  file_id in($ids) and (create_id is null OR create_id=1578891093) and create_time > '{$start} 00:00:00' and create_time < '{$end} 23:59:59' GROUP BY file_id";
        }
        $list = Db::connect($db2)->query($sql);
        foreach ($list as $kk => $vv) {
            $used_count[$vv['file_id']] = $vv['c'];
        }

        // 开启了状态 需要排序的 排序 数据
        $sort_data = [];
        // 未开启 不需要排序的数据
        $no_data = [];

        //把所有的数据分成 两部分 （排序的和不排序的）
        foreach ($old_data as $k1 => $v2) {
            if ($v2['status'] == 2) {
                $sort_data[$k1] = $v2;
            } else {
                $no_data[$k1] = $v2;
            }
        }

        $sort = [];
        $first = []; // 排完序的数据
        //$sort_data = $result['list'];
        $result['list'] = [];
        foreach ($sort_data as $ks => &$vs) {
            if ($vs['type'] == 2) {
                //新系统
                $vs['used_count'] = isset($used_count[$vs['old_id']]) ? $used_count[$vs['old_id']] : 0;
            } else {
                //获取老系统公司的所有下级部门
                $departments = Cache::store('redis')->get('department_tree' . $vs['old_id']);
                if (is_array($departments) && count($departments) > 0) {
                    $departments = implode(',', $departments);
                } else {
                    $departments = 0;
                }
                //获取老系统的 分配数量
                if (!$create_date) {
                    $sql_old = "SELECT count(crm_file.id) as c FROM crm_customer LEFT JOIN crm_file on crm_customer.file_id = crm_file.id WHERE crm_customer.distribution_time > '2020-4-1 00:00:00' and  crm_file.type = 1 AND department_id in ($departments)";
                } else {
                    list($start, $end) = explode('-', str_replace(' ', ' ', $create_date));
                    $sql_old = "SELECT count(crm_file.id) as c FROM crm_customer LEFT JOIN crm_file on crm_customer.file_id = crm_file.id WHERE crm_customer.distribution_time > '{$start} 00:00:00' and crm_customer.distribution_time < '{$end} 23:59:59' and  crm_file.type = 1 AND department_id in ($departments)";
                }
                $count = Db::query($sql_old);
                $count = isset($count[0]['c']) ? $count[0]['c'] : 0;
                //老系统
                $vs['used_count'] = $count;
                if ($vs['total'] > 0) {
                    $vs['used_count'] = $vs['total'];
                }
            }

            $vs['surplus_money'] = $vs['all_money'] - $vs['used_count'] * $vs['sources_price'];
            $sort[$ks] = $vs['surplus_money'];
        }
        //以剩余的 金额 升序 排序
        asort($sort);
        foreach ($sort as $ksort => $vsort) {
            $first[$ksort] = $sort_data[$ksort];
        }
        //合并 排序部分 和不排序的部分
        $all_data = array_merge($first, $no_data);
        //根据分页 截取本页 展示的数据
        $result['list'] = array_slice($all_data, ($page_current - 1) * $rows, 20);
        !empty($this->title) && $this->assign('title', $this->title);
        return $this->fetch('', $result);
    }

    public function dellj()
    {
        $db = Db::name($this->table);
        $db->where('is_rubbish is not null');
        $db->whereNull('is_public');
        $db->whereNull('is_quit');
        $db->where('is_deleted', 0)->delete();
        $this->success('删除成功', 'index6');

    }
}
