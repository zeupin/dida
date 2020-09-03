# bootstrap

在`composer.json`中设置了自动加载本目录中的指定文件：

composer.json

```json
{
  "autoload": {
    "files": [
      "src/Dida/bootstrap/constants.php",
      "src/Dida/bootstrap/functions.php"
    ],
  },
}
```

constants.php 全局常量

```php
define('DS', DIRECTORY_SEPARATOR);
```

functions.php 全局函数

```php
```
