# Dida Framework

Dida Framework 是一个 PHP 轻量级快速开发框架。

- Github: <https://github.com/zeupin/dida>
- Gitee: <https://gitee.com/zeupin/dida>
- Wiki: <https://github.com/zeupin/dida/wiki>

## 运行环境要求

- PHP v5.5 及以上，推荐 PHP v7.0 及以上。
- 开启 `ext-mbstring` 扩展。
- 开启 `ext-json` 扩展。

## 遵循规范

- [x] `PSR-4` 类自动加载规范。
- [x] `PSR-11` 容器规范。

## Web 服务器配置

### Apache 配置

如果在同一个域名下，有多个项目需要 Rewrite，可以按照如下的 `.htaccess` 示例文件进行配置。

```apache
RewriteEngine On

## oa
RewriteBase /oa
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} ^/oa(.*)$
RewriteRule ^(.*)$ /oa/index.php [QSA]

## crm
RewriteBase /crm
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} ^/crm(.*)$
RewriteRule ^(.*)$ /crm/index.php [QSA]
```

## 配置文件

### 数据库配置

```php
return [
    'driver'   => "\\Dida\Db\\Driver\\Mysql",                    // 必填
    'dsn'      => 'mysql:host=localhost;port=3306;dbname=foo',   // 必填
    'username' => 'tom',
    'password' => 'jerry',
    'options'  => [
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT         => true
    ],
];
```

更多 Db 的使用, 请参见 `src/Dida/Db/README.md`

## 项目支持和商业合作

如您觉得 Dida 框架不错，欢迎您使用 **付费技术支持、项目开发、技术合作、小额捐助** 等方式来支持本项目的持续开发和改进。

## 版权和著作权

Dida Framework，代码采用 [MIT](./LICENSE) 版权协议，文档采用 `CC-BY 4.0` 版权协议。

版权所有 (c) 2017-2021 上海宙品信息科技有限公司。<br>
Copyright (c) 2017-2021 Zeupin LLC. <http://zeupin.com>
