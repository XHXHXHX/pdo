<?php

class DB extends DB_Base {

    protected $prefix = '';
    protected $table = '';
    protected $where = [];
    protected $prepareParam = [];
    protected $params = [];
    protected $select = [];
    protected $orderBy = [];
    protected $groupBy = [];
    protected $limit = 0;
    protected $offset = 0;
    protected $having = [];
    protected $leftJoin = [];
    protected $rightJoin = [];
    protected $innerJoin = [];

    public function __construct() {}

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function where($params)
    {
        if(is_array($params))
        {
            foreach ($params as $key => $value)
            {
                if(is_array($value))
                {
                    if(count($value) != 3)
                        throw new Exception('where 参数错误');
                    # eg: ['name', 'like', 'Jerry%']
                    $this->where[$value[0]] = implode(' ', $value);
                    $this->params[$value[0]] = $value[2];
                    $this->prepareParam[$key] = implode(' ', [$value[0], $value[1], ':'.$value[0]]);
                }else{
                    # eg: ['name'=> 'Jerry']
                    $this->where[$key] = $key . ' = '. $value;
                    $this->prepareParam[$key] = ':'.$key;
                    $this->params[$key] = $value;
                }
            }
        }else{
            $count = func_num_args();
            $args = func_get_args();

            if($count == 1)
            {
                throw new Exception('where 参数错误');
            } else if($count == 2)
            {
                # eg: 'name', 'Jerry'
                $this->where[$args[0]] = $args[0] . ' = ' . $args[1];
                $this->params[$args[0]] = $args[1];
                $this->prepareParam[$args[0]] = ':'.$args[0];
            } else if($count == 3)
            {
                # eg: 'name', '!=', 'Jerry'
                $this->where[$args[0]] = implode(' ', $args);
                $this->params[$args[0]] = $args[2];
                $this->prepareParam[$args[0]] = implode(' ', [$args[0], $args[1], ':'.$args[0]]);

            } else {
                throw new Exception('where 参数错误');
            }
        }

        return $this;
    }
    public function whereIn($key, array $ids)
    {
        if(!is_array($ids))
            throw new Exception('ids is array');

        $this->where[$key] = $key . ' in (' . implode(',', $ids) .')';
        $this->prepareParam[$key] = $key . 'in (:' .$key . ')';
        $this->params[$key] = $ids;

        return $this;
    }
    public function whereBetween($key, array $range)
    {
        if(!is_array($range))
            throw new Exception('range is array');

        if(count($range) != 2)
            throw new Exception('range has 2 paraments');

        $this->where[$key] = $key . ' between ' . $range[0] . ' and '. $range[1];
        $this->prepareParam[$key] = $key . ' between :start_' . $range[0] . ' and :end_'.$range[1];
        $this->params['start_'.$key] = $range[0];
        $this->params['end_'.$key] = $range[1];

        return $this;
    }
    public function whereRaw($sql)
    {
        if(!$sql)
            throw new Exception('paraments error');

        $this->where[] = $sql;
        $this->prepareParam[] = $sql;
    }
    public function orWhere($params) {}
    public function select() {}
    public function selectRaw() {}
    public function orderBy() {}
    public function groupBy() {}
    public function limit() {}
    public function offset() {}
    public function having() {}
    public function leftJoin() {}
    public function rightJoin() {}
    public function innerJoin() {}

    public function get() {}        # 获取全部
    public function find() {}       # 获取一条
    public function findIt() {}     # 获取单个字段
    public function pluck() {}      # 同 laravel
    public function update() {}
    public function insert() {}
    public function delete() {}

    public static function beginTransaction() {}
    public static function commit() {}
    public static function callBack() {}


    protected function replaceParam($value, $key)
    {
        if(strpos($value, '%') == 0)
        {
            if(strrpos($value, '%') == strlen($value) - 1)
            {
                $value = '%:' . $key . '%';
            }else{
                $value = '%:' . $key;
            }
        }else{
            $value = ':'.$key;
        }

        return $value;
    }
}