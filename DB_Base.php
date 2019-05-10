<?php

require_once 'pdo.php';

class DB_Base {

    private $pdo;
    private $correctErrorCode = '00000';

    protected $return = [
        'items'     => [],
        'rowNum'    => 0,
        'code'      => 00000,
        'message'   => '',
        'sql'       => '',
    ];

    public function __construct()
    {
        $this->pdo = Mysql::instance();
    }

    # 执行 sql
    protected function _execute($sql, $params = []) {
        if($stmt_key = $this->pdo->execute($sql, $params))
            return false;

        $this->return['code']       = $this->pdo->errorCode($stmt_key);

        if($this->return['code'] != $this->correctErrorCode)
            return $this->errorResult($stmt_key);

        if(stripos($sql, 'SELECT') == 0)
        {
            $this->return['items']  = $this->pdo->getRow($stmt_key);
            $this->return['rowNum'] = count($this->return['items']);
        } else {
            $this->return['rowNum'] = $this->pdo->rowCount($stmt_key);
        }

        $this->return['sql']    = $this->pdo->executeSql($stmt_key);

        return $this->return;

    }
    protected function _select($sql, $params = []) { }
    protected function _insert($sql, $params = []) {}
    protected function _update($sql, $params = []) {}
    protected function _delete($sql, $params = []) {}

    private function errorResult($stmt_key)
    {
        $errorInfo                  = $this->pdo->errorMessage($stmt_key);
        $this->return['message']    = $errorInfo[2];
        $this->getLastSql($stmt_key);

        return $this->return;
    }

    private function getLastSql($stmt_key)
    {
        $this->return['sql']        = $this->pdo->executeSql($stmt_key);
    }

}