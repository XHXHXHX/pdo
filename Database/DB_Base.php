<?php

namespace Database;

use mysql_xdevapi\Exception;

class DB_Base {

    private $pdo;
    private $correctErrorCode = '00000';
    private $stmt_key;

    public $items;
    public $rowNum;
    public $errorCode;
    public $errorMessage;
    public $sql;

    public function __construct()
    {
        $this->pdo = MyPdo::instance();
    }

    # 执行 sql
    protected function _execute($sql, $params = [])
    {
        $this->stmt_key = $this->pdo->execute($sql, $params);
    }

    protected function _select($sql, $params = [])
    {
        $this->_execute($sql, $params);

        $this->resultInfo();

        $this->closePdo();

        return $this->items;
    }
    protected function _insert($sql, $params = [])
    {
        $this->_execute($sql, $params);

        $this->resultInfo(false);

        $this->closePdo();

        return $this->rowNum;
    }
    protected function _update($sql, $params = [])
    {
        $this->_execute($sql, $params);

        $this->resultInfo(false);

        $this->closePdo();

        return $this->rowNum;
    }
    protected function _delete($sql, $params = [])
    {
        $this->_execute($sql, $params);

        $this->resultInfo(false);

        $this->closePdo();

        return $this->rowNum;
    }

    private function resultInfo($is_select = true)
    {
        if($is_select) {
            $this->items =  $this->pdo->getRow($this->stmt_key);
            $this->rowNum = count($this->items);
        } else {
            $this->rowNum = $this->pdo->rowCount($this->stmt_key);
        }

        $this->getLastSql();

        return $this->items;
    }

    private function errorResult()
    {
        $errorInfo                  = $this->pdo->errorMessage($this->stmt_key);
        $this->errorMessage         = $errorInfo[2];
        $this->getLastSql();

        return $this;
    }

    private function getLastSql()
    {
        $this->sql                  = $this->pdo->executeSql($this->stmt_key);
        echo '<br>', "[sql] {$this->sql}";
    }

    private function closePdo()
    {
        $this->pdo->closeStmt($this->stmt_key);
    }

}