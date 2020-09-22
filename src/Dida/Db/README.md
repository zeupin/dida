# Db

## 常用

### 1. 在Model中创建db的引用

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

### 2.  `$this->db->table()`

```php
// 创建一个查询 \Dida\Db\Query
$q = $this->db->table("表名","前缀","别名");
```

### 3. 查询的一些方法，可以链式操作

* 用法1 `where(字段名, 操作, 值)` 。操作默认为=，值默认为null
* 用法2 `where(字段数组, 操作)`。操作默认为=

```php
// 用法1-1：简单查询
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

### 4. 修改WHERE条件的拼接逻辑

* 默认的WHERE条件拼接逻辑为`AND`，可以通过`whereLogic($logic)`修改拼接逻辑。
* whereLogic()只对其后的where设置有效，对之前的无效。
    ```php
    table()->where(条件1)->where(条件2);
    // 结果： (条件1 AND 条件2)

    table()->where(条件1)->where(条件2)->whereOr()->where(条件3)->where(条件4);
    // 结果： ((条件1 AND 条件2) OR 条件3 OR 条件4)

    table()->where(条件1)->where(条件2)->whereOr()->where(条件3)->where(条件4)->whereAnd()->where(条件5);
    // 结果： (((条件1 AND 条件2) OR 条件3 OR 条件4) AND 条件5)
    ```
