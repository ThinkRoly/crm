<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;

class SystemTeam extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'system_team';

    public $timestamps = false;

    public function getAllTeam() {
        return $this->where('status', 1)->get();
    }

    public function getAllTeamById($teamId) {
        $sql = "WITH RECURSIVE sub_team AS (
            SELECT id, name, manager_id, status, parent_id, create_time, update_time,company_id
            FROM system_team
            WHERE id = ?  and status = 1
            UNION ALL
            SELECT d.id, d.parent_id, d.dept_name
            FROM system_team d
            INNER JOIN sub_team st ON d.parent_id = st.id
            WHERE d.status = 1
        )SELECT * FROM sub_team";
        return app('db')->select($sql, [$teamId]);
    }

    private function _createWhere($params) {
        $query = $this->where('status', 1);
        if (isset($params['name']) && $params['name'] !== "") {
            $query = $query->where(['name' => $params['name']]);
        }
        if (isset($params['status']) && $params['status'] !== "") {
            $query = $query->where(['status' => $params['status']]);
        }
        return $query;
    }

    public function getLists($params) {
        $offset = ($params['current'] - 1) * $params['pageSize'];
        $list = $this->_createWhere($params)->orderBy("id", "desc")->skip($offset)->take($params['pageSize'])->get();
        return $list;
    }
    public function getCount($params) {
        return $this->_createWhere($params)->count();
    }

    public function getAllTeamByParentId($parentId = 0) {
        return $this->where('status', 1)->where('parent_id', '=', $parentId)->get();
    }
}
