# Db

## 常用

1. 在Model中创建db的引用

```php
class Model
{
    // db的本地实例
    protected $db;

    public function __construct()
    {
        ...

        // 初始化db
        $this->db = ServiceBus::get("Db");
    }
}
```

2.  `$this->db->table()`

```php
// 创建一个查询 \Dida\Db\Query\Query
$q = $this->db->table("表名","前缀","别名");
```

3. 查询的一些方法，可以链式操作

* 用法1 `where(字段名, 操作, 值)` 。操作默认为=，值默认为null
* 用法2 `where(字段数组, 操作)`。操作默认为=

```php
// 用法1：简单查询
$this->db->table("表名","前缀","别名")->where("username", '=', 'tom')->where("password", '=', 'jerry')->select();
// 对应的WHERE子句为：
// [
//    "sql"    => "WHERE ((`username` = ?) AND (`password` = ?))",
//    "params" => ["tom","jerry"],
// ]


// 用法2-1：多字段查询
$input = [
    "username" => 'tom',
    "password" => 'jerry',
];
$this->db->table("表名","前缀","别名")->where($input)->select();
// 对应的WHERE子句为：
// [
//    "sql"    => "WHERE ((`username` = ?) AND (`password` = ?))",
//    "params" => ["tom","jerry"],
// ]

// 用法2-2：多字段查询，带op
$input = [
    "username" => 'tom',
    "password" => 'jerry',
];
$this->db->table("表名","前缀","别名")->where($input, 'LIKE')->select();
// 对应的WHERE子句为：
// [
//    "sql"    => "WHERE ((`username` LIKE ?) AND (`password` LIKE ?))",
//    "params" => ["tom","jerry"],
// ]
```
