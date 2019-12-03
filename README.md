# Dida Framework

Dida Framework，一个 PHP 轻量级快速开发框架。

- 官网: <https://github.com/zeupin/dida>
- Gitee: <https://gitee.com/zeupin/dida>
- Wiki: <https://github.com/zeupin/dida/wiki>

## 运行环境要求

- PHP v5.5 及以上。推荐 PHP v7.0 及以上。
- 开启 `mb_string` 组件。

## 遵循规范

- [x] `PSR-4` 类自动加载规范。

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

## 版权和著作权

Dida Framework 采用 [MIT](./LICENSE) 协议。

## 项目支持和商业合作

如您觉得 Dida 框架不错，欢迎您使用 **付费技术支持、项目开发、技术合作、小额捐助** 等方式来支持本项目的持续开发和改进。

如您有商业开发的需求合作，敬请联系 <dida@zeupin.com>。专业技术服务，价格实惠厚道！
