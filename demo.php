<?php

require "ProMysql.php";

$ProMysql = new ProMysql();

$select = $ProMysql->table('product_copy')
			->leftJoin('product', 'pro_id', 'con_pro_id')
			->where('pro_id', '<', 100)
			->where('pro_id', '>=', 1)
			->select('pro_id', 'pro_name', 'pro_recordCode', 'pro_str', 'pro_is_suit_gravida', 'pro_otherName', 'pro_type_id', 'pro_safe_level', 'pro_img', 'pro_oldImg')
			->limit(2)
			->get();