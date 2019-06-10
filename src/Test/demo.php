<?php

namespace Test;
require_once '../../vendor/autoload.php';

define('DB_CONFIG_PATH', '../Config/db_config.php');
//require "ProMysql.php";
//
//$ProMysql = new ProMysql();
//
//$select = $ProMysql->table('  product_copy')
//			->leftJoin('product', 'pro_id', 'con_pro_id')
//			->where('pro_id', '<', 100)
//			->where('pro_id', '>=', 1)
//			->select('pro_id', 'pro_name', 'pro_recordCode', 'pro_str', 'pro_is_suit_gravida', 'pro_otherName', 'pro_type_id', 'pro_safe_level', 'pro_img', 'pro_oldImg')
//			->limit(2)
//			->get();
//
//use Database\MyPdo;
use Mypro\DB;

//
//require_once 'Database\MyPdo.php';

spl_autoload_register(function ($class) {
    if ($class) {
        $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
        $file .= '.php';
        if (file_exists($file)) {
            include $file;
            return;
        }
        $file = ".." . DIRECTORY_SEPARATOR . $file;
        if (file_exists($file)) {
            include $file;
            return;
        }
    }
});

$where = [
    ['c', 'like', '%1%\'--\''],
    'a' => 4,
    ['b', 'in', [1, 2, 3]],
];

//$res = DB::table('test')->insert([
//    ['b' => 10, 'c' => 10,  'd' => 10,  'e' => 'i'],
//    ['b' => 11, 'c' => 11,  'd' => 11,  'e' => 'j'],
//    ['b' => 12, 'c' => 12,  'd' => 12,  'e' => 'k'],
//]);

$res = DB::table('class as c')
    ->select('c.name as class_name', 's.name as student_name', 's.age', 's.sex')
    ->leftJoin('relation_class_students as r', 'r.class_id', '=', 'c.id')
    ->leftJoin('students as s', 'r.student_id', '=', 's.id')
    ->where('c.grade', 1)
    ->where('c.class', 1)
    ->get();

echo '<br>', $res ? 'success' : 'faild', '<br>';
var_dump($res);
//MyPdo::instance();
//new MyPdo();
