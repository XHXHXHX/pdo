<?php

namespace pro\Database;

class DB_Base {

    private $pdo;
    private $correctErrorCode = '00000';

    public $items;
    public $rowNum;
    public $errorCode;
    public $errorMessage;
    public $sql;

    public function __construct()
    {
        $this->pdo = Mysql::instance();
    }

    # 执行 sql
    protected function _execute($sql, $params = []) {
        if($stmt_key = $this->pdo->execute($sql, $params))
            return false;

        $this->errorCode = $this->pdo->errorCode($stmt_key);

        if($this->errorCode != $this->correctErrorCode)
            return $this->errorResult($stmt_key);

        return $this->resultInfo($stmt_key, false);
    }

    protected function _select($sql, $params = []) {}
    protected function _insert($sql, $params = []) {}
    protected function _update($sql, $params = []) {}
    protected function _delete($sql, $params = []) {}

    private function resultInfo($stmt_key, $is_select = false)
    {
        if($is_select) {
            $this->items =  $this->pdo->getRow($stmt_key);
            $this->rowNum = count($this->items);
        } else {
            $this->rowNum = $this->pdo->rowCount($stmt_key);
        }

        $this->getLastSql($stmt_key);
        $this->pdo->closeStmt($stmt_key);

        return $this;
    }

    private function errorResult($stmt_key)
    {
        $errorInfo                  = $this->pdo->errorMessage($stmt_key);
        $this->errorMessage         = $errorInfo[2];
        $this->getLastSql($stmt_key);

        return $this->return;
    }

    private function getLastSql($stmt_key)
    {
        $this->sql                  = $this->pdo->executeSql($stmt_key);
    }

}