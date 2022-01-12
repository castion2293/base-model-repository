<?php

namespace Pharaoh\BaseModelRepository\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class AbstractBaseRepository
{
    /**
     * model 物件
     *
     * @var
     */
    protected $model;

    /**
     * DB資料表名稱
     *
     * @var
     */
    protected $table;

    // ======================================================================================================
    // 尋找 READ
    // ======================================================================================================

    /**
     * model scope 過濾條件方式
     *
     * @param $scope
     * @param array $params
     * @param Model|null $model
     * @return \Illuminate\Database\Query\Builder
     */
    protected function scopeQuery($scope, array $params, Model $model = null)
    {
        $builder = (empty($model)) ? $this->model->query() : $model->query();

        foreach ($params as $function => $param) {
            $addFunctionName = Str::of($function)->studly()->prepend('add');
            $functionName = (string)Str::of($function)->camel();
            if (method_exists($scope, $addFunctionName)) {
                $builder = $builder->$functionName($params);
            }
        }

        return $builder;
    }

    /**
     * 取得所有資料
     *
     * @param array $field [要搜尋的欄位, Ex.['id', 'name']]
     * @return mixed
     */
    public function getAll($field = ['*'], $eagerLoad = [])
    {
        return $this->model->with($eagerLoad)->select($field)->get();
    }

    /**
     * 依搜尋條件取得資料(whereIn)
     *
     * @param  string  $whereField [搜尋條件欄位名稱]
     * @param  array   $whereValue [搜尋條件資訊]
     * @param  array   $field      [要搜尋的欄位, Ex.['id', 'name']]
     * @param array $eagerLoad
     * @return mixed
     */
    public function getByWhereIn($whereField = 'id', $whereValue = [], $field = ['*'], $eagerLoad = [])
    {
        return $this->model->with($eagerLoad)->select($field)->whereIn($whereField, $whereValue)->get();
    }

    /**
     * 取得單筆資料
     *
     * @param integer $id [PK]
     * @return mixed
     */
    public function find($id, $eagerLoad = [])
    {
        return $this->model->with($eagerLoad)->find($id);
    }

    /**
     * 取得單筆資料(單一搜尋條件)
     *
     * @param array $where [搜尋條件資訊]
     * @param array $field [要搜尋的欄位, Ex.['id', 'name']]
     * @param array $eagerLoad
     * @return mixed
     */
    public function findByWhere($where, $field = ['*'], $eagerLoad = [])
    {
        return $this->model->with($eagerLoad)->select($field)->where($where)->get();
    }

    /**
     * 取得單筆資料 從寫入資料庫取得
     *
     * @param  integer  $id  [PK]
     * @return mixed
     */
    public function findWriteConnect($id)
    {
        return $this->model->onWriteConnection()->find($id);
    }

    /**
     * 取得單筆資料 從寫入資料庫取得 並加排他鎖
     *
     * @param  integer  $id  [PK]
     * @return mixed
     */
    public function findWriteConnectByLockForUpdate($id)
    {
        return $this->model->onWriteConnection()->lockForUpdate()->find($id);
    }

    /**
     * 尋找在某個欄位有重複的數值及個數
     *
     * @param string $field
     * @param array $where
     * @param int $havingCount 取多少以上
     * @return mixed
     */
    public function findDuplicateValue(string $field, array $where = [], int $havingCount = 1)
    {
        return $this->model->selectRaw("{$field}, COUNT(*) AS {$field}_count")
            ->where($where)
            ->where($field, '<>', '')
            ->groupBy($field)
            ->having("{$field}_count", '>=', $havingCount)
            ->get();
    }

    // ======================================================================================================
    // 新增 CREATE
    // ======================================================================================================

    /**
     * 新增資料
     *
     * @param array $parameters [新增資料陣列]
     * @return mixed
     */
    public function store(array $parameters = [])
    {
        return $this->model->create($parameters);
    }

    /**
     * 新增多筆資料
     *
     * @param array $parameters [新增資料陣列]
     * @return mixed
     */
    public function insertMuti(array $parameters = [])
    {
        return $this->model->insert($parameters);
    }

    // ======================================================================================================
    // 更新 UPDATE
    // ======================================================================================================

    /**
     * 更新單筆資料
     *
     * @param integer $id [PK]
     * @param array $parameters [更新資料陣列]
     * @return boolean
     */
    public function update($id, array $parameters = [])
    {
        return $this->model->find($id)->update($parameters);
    }

    /**
     * 更新單筆資訊 根據指定欄位
     *
     * @param $where
     * @param array $parameters
     * @return mixed
     */
    public function updateByWhere($where, array $parameters = [])
    {
        return $this->model->where($where)->update($parameters);
    }

    /**
     * 更新多筆資料
     *
     * @param array $data [更新條件資料]
     * @param array $parameters [更新資料陣列]
     * @param string $field [更新條件名稱]
     * @return boolean
     */
    public function updateMuti($data, array $parameters = [], string $field = 'id')
    {
        return $this->model->whereIn($field, $data)->update($parameters);
    }

    /**
     * 資料新增，存在則更新
     *
     * @param array $where [條件]
     * @param array $parameters [更新資料參數]
     * @return mixed
     */
    public function updateOrInsert(array $where, array $parameters)
    {
        return $this->model->updateOrInsert($where, $parameters);
    }

    /**
     * 批次更新 or 新增資料
     *
     * @param array $parameters
     * @return mixed
     */
    public function updateOrCreateMulti(array $parameters)
    {
        $fields = array_keys(Arr::first($parameters));

        $sql = '';
        foreach ($parameters as $parameter) {
            $sql .= (($sql != '') ? ', ' : '') . '(' . implode(', ', array_values($parameter)) . ")";
        }

        $duplicate = '';
        foreach ($fields as $field) {
            $duplicate .= ($duplicate != '') ? ', ' : '';
            $duplicate .= $field . ' = VALUES(' . $field . ')';
        }

        $table = $this->model->getTable();
        $sqlField = implode(' ,', $fields);

        return DB::statement(
            "INSERT INTO `" . $table . "` (" . $sqlField . ") VALUES " . $sql . " ON DUPLICATE KEY UPDATE " . $duplicate . " ;"
        );
    }

    /**
     * 一次更新多筆數據
     *
     * @param string $setField [欲更新的欄位名稱]
     * @param string $caseField [查詢依據欄位名稱]
     * @param array $setValue [愈設定的數值]
     */
    public function updateMultiRows(string $setField, string $caseField, array $setValue)
    {
        $sql = '';
        foreach ($setValue as $key => $value) {
            $sql .= 'WHEN ' . $caseField . ' = ' . $key . ' THEN ' . $value . ' ';
        }
        $ids = implode(',', array_keys($setValue));

        $table = $this->model->getTable();

        DB::statement(
            'UPDATE ' . $table .' SET ' . $setField . ' = (CASE ' . $sql .  'END) WHERE ' . $caseField . ' IN (' . $ids . ') '
        );
    }

    /**
     * 增加數量
     *
     * @param array $where
     * @param string $field
     * @param $num
     */
    public function increment(array $where, string $field, $num)
    {
        $this->model->where($where)
            ->increment($field, $num);
    }

    /**
     * 減少數量
     *
     * @param array $where
     * @param string $field
     * @param $num
     */
    public function decrement(array $where, string $field, $num)
    {
        $this->model->where($where)
            ->decrement($field, $num);
    }

    // ======================================================================================================
    // 刪除 DELETE
    // ======================================================================================================

    /**
     * 依條件刪除資料
     *
     * @param $where
     * @return boolean
     */
    public function deleteByWhere(array $where)
    {
        return $this->model->where($where)->delete();
    }
}
