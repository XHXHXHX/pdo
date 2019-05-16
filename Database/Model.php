<?php

namespace pro\Database;

class Model {

    protected $db;
    protected $result;
    protected $prepare;
    protected $bindParam;
    protected $sql = [
        'select' => '',
        'table' => '',
        'join' => '',
        'where' => '',
        'group' => '',
        'order' => '',
        'having' => '',
        'offset' => '',
        'limit' => '',
    ];

    public function __construct(DB $db)
    {
        $this->db = $db;
        $this->dealSqlAttr();
    }

    public function __get($key)
    {
        return $this->result->$key;
    }

    public function select(array $pluck_arr = [])
    {
        $sql = $this->makeSql('select');

        $this->result = $this->db->execute($sql, $this->bindParam);

        return $this;
    }

    protected function makeSql($type)
    {
        switch ($type) {
            case 'select':
                return $this->makeSqlSelect();
                break;
            case 'insert':
                break;
            case 'update':
                break;
            case 'delete':
                break;
        }
    }

    protected function makeSqlSelect()
    {
        $sql = '';

        $sql .= 'select ' . $this->sql['select'];

        $sql .= ' from ' . $this->sql['table'];

        if($this->sql['where'])
            $sql .= ' where ' . $this->sql['where'];
        if($this->sql['join'])
            $sql .= ' ' . $this->sql['join'];
        if($this->sql['group'])
            $sql .= ' group by ' . $this->sql['group'];
        if($this->sql['order'])
            $sql .= ' order by ' . $this->sql['order'];
        if($this->sql['having'])
            $sql .= ' having ' . $this->sql['having'];
        if($this->sql['offset'])
            $sql .= ' offset ' . $this->sql['offset'];
        if($this->sql['limit'])
            $sql .= ' limit ' . $this->sql['limit'];

        return $sql;
    }

    /*处理sql属性*/
    protected function dealSqlAttr()
    {
        $this->dealSqlAttrSelect();
        $this->dealSqlAttrTable();
        $this->dealSqlAttrJoin();
        $this->dealSqlAttrWhere();
        $this->dealSqlAttrGroup();
        $this->dealSqlAttrOrder();
        $this->delaSqlAttrHaving();
        $this->delaSqlAttrOffset();
        $this->delaSqlAttrLimit();
    }

    protected function dealSqlAttrSelect()
    {
        $this->sql['select'] = impldoe(',', $this->db->select);
    }

    protected function dealSqlAttrTable()
    {
        $this->sql['table'] = $this->db->prefix . $this->db->table;
    }

    protected function dealSqlAttrJoin()
    {
        $join = [];

        foreach ($this->db->join as $value) {

            $join_string = $value['type'] . ' ' . $this->db->prefix . $value['table'];

            if($value['alias'])
                $join_string .= ' AS '.$value['alias'];

            $join_string .= ' ON ' . $value['table_field'] . ' ' . $value['operator'] . ' ' . $value['other_field'];

            $join[] = $join_string;
        }

        $this->sql['join'] = implode(' ', $join);
    }

    protected function dealSqlAttrWhere()
    {
        $where = [];
        foreach ($this->db->where as $value) {

            $where_string = count($where) == 0 ? '' : $value['relation_type'];

            if ($value['operator'] == 'in')
                $where_string .=  " {$value['field']} in (" . implode(',', array_fill(0, count($value['value'], '?'))) . ")";
            else if ($value['operator'] == 'between')
                $where_string .= " {$value['field']} between {$value['value'][0]} and {$value['value'][1]}";
            else
                $where_string .= " {$value['field']} {$value['operator']} {$value['value']}";

            $where[] = $where_string;
            $this->bindParam = array_merge($this->bindParam, $value['value']);
        }

        $this->sql['where'] = implode(' ', $where);
    }

    protected function dealSqlAttrGroup()
    {
        $this->sql['group'] = $this->db->groupBy;
    }

    protected function dealSqlAttrOrder()
    {
        $this->sql['order'] = implode(',', array_map(function($value){
            return implode(' ', $value);
        }, $this->db->orderBy));
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


}
