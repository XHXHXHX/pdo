# pro

#基于PRO的一个简单地ORM

[GitHub 项目地址](https://github.com/XHXHXHX/pro)

#在用原生写脚本的时候怀念起框架中封装好的ORM，所以仿照laravel写了这个简洁版的ORM

##eg:
```
  $ProMysql->table('product_copy')
			->leftJoin('product', 'pro_id', 'con_pro_id')
			->where('pro_id', '<', 100)
			->where('pro_id', '>=', 1)
			->select('pro_id', 'pro_name')
			->limit(2)
			->get();
```
