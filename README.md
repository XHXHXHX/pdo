# pro

#基于PRO的一个简单地ORM

[GitHub 项目地址](https://github.com/XHXHXHX/pro)

#在用原生写脚本的时候怀念起框架中封装好的ORM，所以仿照laravel写了这个简洁版的ORM，可以链式操作。

##Example:
```Php
  $ProMysql->table('product_copy')
			->leftJoin('product', 'pro_id', 'con_pro_id')
			->where('pro_id', '<', 100)
			->where('pro_id', 1)
			->select('pro_id', 'pro_name')
			->limit(2)
			->get();
```

#实现功能

###条件函数

* table()
* select()
* leftJoin()
* where()       支持数组或多参数形式
* orWhere()
* group()
* order()
* limit()

###操作函数
* count()
* findIt()
* find()
* get()
* insert()
* update()
* delete()
