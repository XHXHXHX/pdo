<?php

namespace pro;
//define('DB_CONFIG_PATH', 'db_config.php');
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

use Database\DB;

spl_autoload_register(function ($class) {
    if ($class) {
        $file = str_replace('\\', '/', $class);
        $file .= '.php';
        echo $file,'<br>';
        if (file_exists($file)) {
            include $file;
        }
    }
});

$res = new DB();
//$res = DB::table('test')->select('a')->where('a', 1)->get();
//var_dump($res);

//new \pro\Database\test;



//namespace pro\Database;
//
//class test{
//    public function __construct()
//    {
//        echo 2;
//    }
//}

//setValue();

