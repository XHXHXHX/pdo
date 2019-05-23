<?php

namespace Exception;

class MyException extends \Exception
{

    public static function PDOException(\PDOException $e)
    {
        $error_message = $e->getMessage();
        $sql = self::getDebugSql($e->getTrace());

        echo $error_message;
        echo '<br>';
        echo "Debug SQL: [ {$sql} ]";
    }

    protected static function getDebugSql($data)
    {
        foreach ($data as $value) {
            if(!empty($value['args'])) {
                $sql = $value['args'][0];
                $param = $value['args'][1];
                break;
            }
        }

        $param = array_map(function($item){
            if(is_string($item))
                $item = "'{$item}'";
            return $item;
        }, $param);

        return preg_replace(array_fill(0, count($param), '/\?/'), $param, $sql, 1);
    }

}