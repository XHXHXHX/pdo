<?php

namespace Mypro;

class DB extends DB_Base {

    public $prefix = 'gm_';
    public $table = [];
    public $where = [];
    public $join = [];

    public $select = [];
    public $orderBy = [];
    public $groupBy = '';
    public $limit = 0;
    public $offset = 0;
    public $having = '';

    public $update;
    public $increment = [];

    public $insert;

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

    public static function table($table, $alias = '')
    {
        $instance = new self;

        if(empty($alias))
            list($table, $alias) = $instance->splitAlias($table);

        $instance->table['table'] = $table;
        $instance->table['alias'] = $alias;

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
        if(is_array($field))
            return $this->addArrayWhere($field, $relation_type);

        if(is_null($operator) && is_null($value))
            $value = $operator = '';

        if(is_null($value)){
            $value = $operator;
            $operator = '=';
        }

        if(strpos($field, '.') !== false) {
            $field = explode('.', $field);
            $field[1] = "`{$field[1]}`";
            $field = implode('.', $field);
        }else{
            $field = "`{$field}`";
        }


        $this->where[] = compact(
            'field', 'operator', 'value', 'relation_type'
        );

        return $this;
    }

    public function whereIn($key, array $ids, $relation_type = 'and')
    {
        return $this->where($key, 'in', $ids, $relation_type);
    }

    public function whereNotIn($key, array $ids, $relation_type = 'and')
    {
        return $this->where($key, 'not in', $ids, $relation_type);
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

    public function select(...$field)
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
        return $this;
    }

    public function orderByDesc($field)
    {
        return $this->orderBy($field, 'desc');
    }

    public function groupBy($value)
    {
        $this->groupBy = $value;
        return $this;
    }

    public function limit($num)
    {
        $this->limit = $num;
        return $this;
    }

    public function offset($num)
    {
        $this->offset = $num;
        return $this;
    }

    public function having($string)
    {
        $this->having = $string;
        return $this;
    }

    public function join($type, $table, $table_field, $operator, $other_field = '', $alias = '')
    {
        list($table, $alias) = $this->splitAlias($table);

        $this->join[] = compact(
          'type', 'table', 'table_field', 'operator', 'other_field', 'alias'
        );
        return $this;
    }

    public function leftJoin($table, $table_field, $operator, $other_field = '', $alias = '')
    {
        return $this->join('left join', $table, $table_field, $operator, $other_field, $alias);
    }

    public function rightJoin($table, $table_field, $operator, $other_field = '', $alias = '')
    {
        return $this->join('right join', $table, $table_field, $operator, $other_field, $alias);
    }

    public function innerJoin($table, $table_field, $operator, $other_field = '', $alias = '')
    {
        return $this->join('inner join', $table, $table_field, $operator, $other_field, $alias);
    }

    # 获取全部
    public function get($field = [])
    {
        $this->selectConvenient($field);

        return $this->execute(Model::select($this));
    }

    # 获取一条
    public function find()
    {
        $this->selectConvenient();

        $this->limit = 1;

        $row = $this->execute(Model::select($this));

        return $row[0] ?? [];
    }

    # 获取单个字段
    public function findIt($field)
    {
        $this->select = [$field];

        $this->limit = 1;

        $row = $this->execute(Model::select($this));

        return $row[0][$field] ?? '';
    }

    # 同 laravel
    public function pluck($value, $key = '')
    {
        $this->select = [$value];
        if($key)
            $this->select[] = $key;

        $row = $this->execute(Model::select($this));

        if($key)
            return array_column($row, $value, $key);
        else
            return array_column($row, $value);
    }

    /*
     * 自增
     *
     * ['age', 'birth']
     * [['age', '+ 1], ['birth', '- 3]]
     * */
    public function increment($field, $value = '+ 1')
    {
        if(!is_array($field)) {
            $this->increment[] = compact('field', 'value');
            return $this;
        }else{
            foreach ($field as $item) {
                if(is_array($item))
                    $this->increment($item[0], $item[1] ?? 1);
                else
                    $this->increment($item);
            }
        }
    }

    /* UPDATE
     *
     * @param   data    array(hash 一维数组)   更新数据
     *
     * @return  int     影响行数*/
    public function update(array $data = [])
    {
        if(empty($data) && empty($this->increment))
            throw new \Exception('别闹');

        $this->update = $data;

        return $this->execute(Model::update($this), 'update');

    }
    public function insert(array $data)
    {
        if(empty($data))
            throw new \Exception('别闹');

        $this->insert = $data;

        return $this->execute(Model::insert($this), 'insert');

    }
    public function delete()
    {
        return $this->execute(Model::delete($this), 'delete');
    }

    public function softDelete() {}

    public function execute($sql_arr, $type = 'select')
    {
        $func = '_'.$type;
        var_dump($sql_arr);

        return $this->$func($sql_arr['sql'], $sql_arr['params']);
    }

    public static function beginTransaction() {}
    public static function commit() {}
    public static function callBack() {}

    /*便捷查询*/
    protected function selectConvenient($field = [])
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

    protected function splitAlias($str)
    {
        $alias = '';

        if(stripos($str, 'as') !== FALSE) {
            $str = explode(' ', $str);
            $alias = $str[2];
            $str = $str[0];
        }
        return [$str, $alias];
    }
}