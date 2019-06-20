# pro

# 基于PDO的一个简单地ORM

[GitHub 项目地址](https://github.com/XHXHXHX/pro)

在用原生写脚本的时候怀念起框架中封装好的ORM，所以仿照laravel写了这个简版的ORM，可以链式操作。

#### SELECT:

```php
 DB::table('class as c')
        ->select('c.name as class_name', 's.name as student_name', 's.age', 's.sex')
        ->leftJoin('relation_class_students as r', 'r.class_id', '=', 'c.id')
        ->leftJoin('students as s', 'r.student_id', '=', 's.id')
        ->where('c.grade', 1)
        ->where('c.class', 1)
        ->get();
```

#### UPDATE

```php
    DB::table('class')
        ->where('grade', 2)
        ->where('class', 1)
        ->update([
            'teach' => '李老师',
        ]);
```

#### INSERT

```php
    DB::table('class')
        ->insert([
            'grade' => 3,
            'class' => 1,
            'name'  => '三年1班'
            'teach' => '李老师',
        ]);
```

### 安装

`composer require xhxhx/mypro v1.0.2`

### 实现功能

##### 条件函数

* table()
* select()
* leftJoin()
* where()       支持数组或多参数形式
* orWhere()
* whereIn
* whereBetween
* group()
* order()
* offset()
* limit()

###操作函数

* count()
* findIt()
* find()
* get()
* insert()
* update()
* delete()



数据库配置在Config/db_config中
