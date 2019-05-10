<?php
//define('DB_CONFIG_PATH', 'db_config.php');
//require "ProMysql.php";
//
//$ProMysql = new ProMysql();
//
//$select = $ProMysql->table('product_copy')
//			->leftJoin('product', 'pro_id', 'con_pro_id')
//			->where('pro_id', '<', 100)
//			->where('pro_id', '>=', 1)
//			->select('pro_id', 'pro_name', 'pro_recordCode', 'pro_str', 'pro_is_suit_gravida', 'pro_otherName', 'pro_type_id', 'pro_safe_level', 'pro_img', 'pro_oldImg')
//			->limit(2)
//			->get();

require 'pdo.php';

$DB = Mysql::instance('db_config.php');
$sql = 'SELECT * FROM test WHERE a = :a';
$result = $DB->execute($sql, ['a'=>1]);
var_dump($DB->getExecuteSql($result));

//setValue();
;

//  最终Util