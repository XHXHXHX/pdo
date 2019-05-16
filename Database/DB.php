<?php

namespace pro\Database;

class DB extends DB_Base {

    public $prefix = '';
    public $table = '';
    public $where = [];
    public $select = [];
    public $join = [];
    public $orderBy = [];
    public $groupBy = '';
    public $limit = 0;
    public $offset = 0;
    public $having = '';

    protected $defaultLimit = 15;

    protected $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
        'like', 'like binary', 'not like', 'ilike',
        '&', '|', '^', '<<', '>>',
        'rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to', 'not ilike', '~~*', '!~~*',
        'between',
    ];

    public function __construct() {
        parent::__construct();
    }

    public static function table($table)
    {
        $instance = new self;
        $instance->table = $table;
        return $instance;
    }

    protected function addArrayWhere($field, $relation_type)
    {
        foreach ($field as $key => $value) {
            if(is_array($value)) {
                # eg: ['name', '!=', 'Jerry']
                $this->where[] = [
                    'field'         =>  array_shift($value),
                    'operator'      =>  array_shift($value),
                    'value'         =>  array_shift($value),
                    'relation_type' =>  $relation_type
                    ];
            }else{
                # eg: ['name' => 'Jerry']
                $this->where[] = [
                    'field'         =>  $key,
                    'operator'      =>  '=',
                    'value'         =>  $value,
                    'relation_type' =>  $relation_type
                ];
            }
        }

        return $this;
    }

    public function where($field, $operator = null, $value = null, $relation_type = 'and')
    {
        if(!$field || ($field && !$operator))
            return $this;       # 抛异常

        if(is_array($field))
            return $this->addArrayWhere($field, $relation_type);

        if(is_null($value)){
            $value = $operator;
            $operator = '=';
        }

        $this->where[] = compact(
            'field', 'operator', 'value', 'relation_type'
        );

        return $this;
    }

    public function whereIn($key, array $ids, $relation_type = 'and')
    {
        return $this->where($key, 'in', implode(',', $ids), $relation_type);
    }

    public function whereNotIn($key, array $ids, $relation_type = 'and')
    {
        return $this->where($key, 'not in', implode(',', $ids), $relation_type);
    }

    public function whereBetween($key, array $range, $relation_type = 'and')
    {
        return $this->where($key, 'between', $range, $relation_type);
    }

    public function whereNotBetween($key, array $range, $relation_type = 'and')
    {
        return $this->where($key, 'not between', $range, $relation_type);
    }

    public function whereLike($key, $value, $relation_type = 'and')
    {
        return $this->where($key, 'like', $value, $relation_type);
    }

    public function whereNotLike($key, $value, $relation_type = 'and')
    {
        return $this->where($key, 'not like', $value, $relation_type);
    }

    public function whereRaw($sql)
    {
        if(!$sql)
            throw new Exception('paraments error');

        $this->where[] = [
            'field'         => $sql,
            'operator'      => '',
            'value'         => '',
            'relation_type' => '',
        ];

        return $this;
    }

    public function orWhere($field, $operator = null, $value = null)
    {
        return $this->where($field, $operator, $value, 'or');
    }

    public function orWhereIn($key, array $ids)
    {
        return $this->whereIn($key, $ids, 'or');
    }

    public function orWhereNotIn($key, array $ids)
    {
        return $this->whereNotIn($key, $ids, 'or');
    }

    public function orWhereBetween($key, array $range)
    {
        return $this->whereBetween($key, $range, 'or');
    }

    public function orWhereNotBetween($key, array $range)
    {
        return $this->whereNotBetween($key, $range, 'or');
    }

    public function orWhereLike($key, $value)
    {
        return $this->whereLike($key, $value, 'or');
    }

    public function orWhereNotLike($key, $value)
    {
        return $this->whereNotLike($key, $value, 'or');
    }

    public function select(array $field = ['*'])
    {
        $this->select = $field;
        return $this;
    }

    public function selectRaw($string)
    {
        $this->select[] = $string;
        return $this;
    }

    public function orderBy($field, $option = 'asc')
    {
        $this->orderBy[] = $field . ' ' . $option;
        return;
    }

    public function orderByDesc($field)
    {
        return $this->orderBy($field, 'desc');
    }

    public function groupBy($value)
    {
        $this->groupBy = $value;
        return;
    }

    public function limit($num)
    {
        $this->limit = $num;
        return $this;
    }

    public function offset($num)
    {
        $this->offset = $num;
        return $num;
    }

    public function having($string)
    {
        $this->having = $string;
        return $this;
    }

    public function join($type, $table, $table_field, $operator, $other_field = '', $alias = '')
    {
        $this->join[] = compact(
          'type', 'table', 'table_field', 'operator', 'other_field', 'alias'
        );
        return $this;
    }

    public function leftJoin($table, $table_field, $operator, $other_field = '', $alias = '')
    {
        return $this->join('LEFTJOIN', $table, $table_field, $operator, $other_field, $alias);
    }

    public function rightJoin($table, $table_field, $operator, $other_field = '', $alias = '')
    {
        return $this->join('RIGHTJOIN', $table, $table_field, $operator, $other_field, $alias);
    }

    public function innerJoin($table, $table_field, $operator, $other_field = '', $alias = '')
    {
        return $this->join('INNERJOIN', $table, $table_field, $operator, $other_field, $alias);
    }

    # 获取全部
    public function get($field = [])
    {
        $this->selectConvenient($field);

        $model = new Model($this);

        return $model->select();
    }

    # 获取一条
    public function find($field = [])
    {
        $this->selectConvenient($field);

        $this->limit = 1;

        $model = new Model($this);

        return $model->select();
    }

    # 获取单个字段
    public function findIt($field)
    {
        $this->select = [$field];

        $model = new Model($this);

        return $model->select();
    }

    # 同 laravel
    public function pluck($value, $key = '')
    {
        $this->select = [$value, $key];

        $model = new Model($this);

        return $model->pluck([$key => $value]);
    }
    public function update() {}
    public function insert() {}
    public function delete() {}

    public function execute($sql, array $params = [])
    {
        return $this->_execute($sql, $params);
    }

    public static function beginTransaction() {}
    public static function commit() {}
    public static function callBack() {}

    /*便捷查询*/
    protected function selectConvenient($field)
    {
        if(!$field) {
            if(empty($this->select))
                $this->select = ['*'];
            return;
        }

        if(is_array($field))
            $this->select = array_merge($this->select, $field);
        else
            $this->select[] = $field;

    }


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