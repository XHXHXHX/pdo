<?php

namespace Database;

class Model
{

    protected $db;
    protected $result;
    protected $prepare;
    protected $bindParam = [];
    protected $sql = [
        'select'        => '',
        'table'         => '',
        'join'          => '',
        'where'         => '',
        'group'         => '',
        'order'         => '',
        'having'        => '',
        'offset'        => '',
        'limit'         => '',
        'update'        => '',
        'insert'        => [],
        'insert_field'  => '',
    ];

    public function __construct(DB $db, $type)
    {
        $this->db = $db;
        $this->dealSqlAttr($type);
    }

    public function __get($key)
    {
        if (isset($this->sql[$key]))
            return $this->sql[$key];
        return false;
    }

    public static function select(DB $db)
    {
        $instance = new self($db, 'select');

        $res['sql'] = $instance->makeSql('select');
        $res['params'] = $instance->bindParam ?: [];

        return $res;
    }

    public static function update(DB $db)
    {
        $instance = new self($db, 'update');

        $res['sql'] = $instance->makeSql('update');
        $res['params'] = $instance->bindParam ?: [];

        return $res;
    }

    public static function insert(DB $db)
    {
        $instance = new self($db, 'insert');

        $res['sql'] = $instance->makeSql('insert');
        $res['params'] = $instance->bindParam ?: [];

        return $res;
    }

    public static function delete(DB $db)
    {
        $instance = new self($db, 'delete');

        $res['sql'] = $instance->makeSql('delete');
        $res['params'] = $instance->bindParam ?: [];

        return $res;
    }

    protected function makeSql($type)
    {
        switch ($type) {
            case 'select':
                return $this->makeSelectSql();
            case 'insert':
                return $this->makeInsertSql();
            case 'update':
                return $this->makeUpdateSql();
            case 'delete':
                return $this->makeDeleteSql();
        }
    }

    protected function makeSelectSql()
    {
        $sql = '';

        $sql .= 'select ' . $this->sql['select'];

        $sql .= ' from ' . $this->sql['table'];

        if ($this->sql['join'])
            $sql .= ' ' . $this->sql['join'];
        if ($this->sql['where'])
            $sql .= ' where ' . $this->sql['where'];
        if ($this->sql['group'])
            $sql .= ' group by ' . $this->sql['group'];
        if ($this->sql['order'])
            $sql .= ' order by ' . $this->sql['order'];
        if ($this->sql['having'])
            $sql .= ' having ' . $this->sql['having'];
        if ($this->sql['limit'])
            $sql .= ' limit ' . $this->sql['limit'];
        if ($this->sql['offset'])
            $sql .= ' offset ' . $this->sql['offset'];

        return $sql;
    }

    protected function makeInsertSql()
    {
        $sql = '';

        $sql .= 'insert into ' . $this->sql['table'];

        $sql .= ' ' . $this->sql['insert_field'];

        $sql .= ' VALUES ' . $this->sql['insert'];

        return $sql;
    }

    protected function makeUpdateSql()
    {
        $sql = '';

        $sql .= 'update ' . $this->sql['table'];

        $sql .= ' set ' . $this->sql['update'];

        $sql .= ' where ' . $this->sql['where'];

        return $sql;
    }

    protected function makeDeleteSql()
    {
        $sql = '';

        $sql .= 'delete from ' . $this->sql['table'];

        $sql .= ' where ' . $this->sql['where'];

        return $sql;
    }

    /*处理sql属性*/
    protected function dealSqlAttr($type)
    {
        $this->dealSqlAttrTable();

        switch ($type) :
            case 'select':
                $this->dealSqlAttrSelect();
                $this->dealSqlAttrJoin();
                $this->dealSqlAttrWhere();
                $this->dealSqlAttrGroup();
                $this->dealSqlAttrOrder();
                $this->delaSqlAttrHaving();
                $this->delaSqlAttrOffset();
                $this->delaSqlAttrLimit();
                break;
            case 'insert':
                $this->dealSqlAttrInsert();
                $this->dealSqlAttrIncrement();
                break;
            case 'update':
                $this->dealSqlAttrUpdate();
                $this->dealSqlAttrWhere();
                break;
            case 'delete':
                $this->dealSqlAttrWhere();
                break;

        endswitch;
    }

    protected function dealSqlAttrInsert()
    {
        $this->insertArrayValDeal($this->db->insert);
        $this->insertArrayKeyDeal($this->db->insert);

        $this->sql['insert'] = implode(',', $this->sql['insert']);
    }

    protected function insertArrayValDeal($array)
    {
        foreach ($array as $value) {
            if(is_array($value)) {
                $this->insertArrayValDeal($value);
                continue;
            }

            $this->bindParams($value);
        }

        if(array_keys($array) !== range(0, count($array) - 1))
        $this->sql['insert'][] = '(' . implode(",", array_fill(0, count($array), "?")) . ')';
    }

    protected function insertArrayKeyDeal($array)
    {
        $keys = array_keys($array);

        if($keys === range(0, count($array) - 1))
            $keys = array_keys($array[0]);

        $this->sql['insert_field'] = '(' . implode(',', $keys) . ')';
    }



    protected function dealSqlAttrUpdate()
    {
        if(empty($this->db->update)) return;

        $data = [];

        foreach ($this->db->update as $key => $value) {
                $data[] = "`{$key}` = ?";
            $this->bindParams($value);
        }

        $this->sql['update'] = implode(',', $data);
    }

    protected function dealSqlAttrIncrement()
    {
        if(empty($this->db->increment)) return;

        $data = [];

        foreach ($this->db->increment as $value) {
            $data[] = "`{$value['field']}` = {$value['field']} {$value['value']}";
        }

        $char = empty($this->sql['update']) ? '' : ',';
        $this->sql['update'] =  $char . implode(',', $data);
    }

    protected function dealSqlAttrSelect()
    {
        $this->sql['select'] = implode(',', $this->db->select);
    }

    protected function dealSqlAttrTable()
    {
        $this->sql['table'] = "`{$this->db->prefix}{$this->db->table['table']}`";

        if(!empty($this->db->table['alias']))
            $this->sql['table'] .= "as {$this->db->table['alias']}";
    }

    protected function dealSqlAttrJoin()
    {
        if(empty($this->db->join)) return;

        $join = [];

        foreach ($this->db->join as $value) {

            $join_string = $value['type'] . '`' . $this->db->prefix . $value['table'] . '`';

            if ($value['alias'])
                $join_string .= ' AS ' . $value['alias'];

            $join_string .= ' ON ' . $value['table_field'] . ' ' . $value['operator'] . ' ' . $value['other_field'];

            $join[] = $join_string;
        }

        $this->sql['join'] = implode(' ', $join);
    }

    protected function dealSqlAttrWhere()
    {
        $where = [];
        foreach ($this->db->where as $value) {

            if ($value['operator'] == 'in' || $value['operator'] == 'not in')
                $where[] = $this->whereInSql($value);
            else if ($value['operator'] == 'between' || $value['operator'] == 'not between')
                $where[] = $this->whereBetweenSql($value);
            else {
                $where[] .= "{$value['relation_type']} {$value['field']} {$value['operator']} ?";
            }
            $this->bindParams($value['value']);
        }

        $where = ltrim(ltrim(implode(' ', $where), 'and'), 'or');

        $this->sql['where'] = $where;
    }

    protected function whereInSql($value)
    {
        return "{$value['relation_type']} {$value['field']} {$value['operator']} (" . implode(',', array_fill(0, count($value['value']), '?')) . ")";
    }

    protected function whereBetweenSql($value)
    {
        return "{$value['relation_type']} {$value['field']} {$value['operator']} ? and ?";
    }

    protected function dealSqlAttrGroup()
    {
        $this->sql['group'] = $this->db->groupBy;
    }

    protected function dealSqlAttrOrder()
    {

        $this->sql['order'] = implode(',', $this->db->orderBy);
    }

    protected function delaSqlAttrHaving()
    {
        $this->sql['having'] = $this->db->having;
    }

    protected function delaSqlAttrOffset()
    {
        if($this->db->offset)
            $this->sql['offset'] = $this->db->offset;
    }

    protected function delaSqlAttrLimit()
    {
        if($this->db->limit)
            $this->sql['limit'] = $this->db->limit;
    }

    protected function bindParams($value)
    {
        $value = is_array($value) ? $value : (array)$value;
        $this->bindParam = array_merge($this->bindParam, $value);
    }


}
