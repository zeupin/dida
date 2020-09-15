## 基本概念

### `Service`

`Service` 的方法都是静态方法。一个典型的Service类的结构如下：

```php
class FooService extends Dida\Service
{
    // Service的方法都必须声明成静态方法！
    // 注意下面几个方法都是static的！
    public static function bar1() {...}
    protected static function bar2() {...}
    public static function bar3() {...}
    ...
}
```

### `Provider`

`Provider` 是一个普通类，方法既可以是普通方法，也可以是静态方法。一个典型的Provider结构如下：

```php
class FooProvider
{
    // 普通方法，静态和非静态均可
    public function bar1() {...} // 普通方法
    public static function bar2() {...} // 静态方法
    protected function bar3() {...}
    ...
}
```

### `ServiceProvider`

`ServiceProvider` 就是先生成一个Provider实例，然后用魔术方法__callStatic，把某个Service的静态方法映射到Provider实例的普通方法或者静态方法。

```
$obj = new FooServiceProvider();

FooService::bar1() 通过魔术方法 __callStatic 映射到 $obj->bar1()
FooService::bar2() 通过魔术方法 __callStatic 映射到 $obj->bar2()
FooService::bar3() 通过魔术方法 __callStatic 映射到 $obj->bar3()
```

## `ServiceBus`

`ServiceBus` 用于登记和管理App中需要用到的若干个Services
