# 企应用
企应用是一款专门针对现在流行的小程序开发的小程序内容管理系统。支持微信小程序、百度智能小程序、支付宝小程序、字节跳动小程序、快应用等

# 目录

1. [文件及目录说明](#文件和目录说明)
2. [配置文件](#配置文件)
3. [Web服务器配置](#Web服务器配置)
    1. [Nginx](#Nginx)
    2. [Apache](#Apache)

## 文件和目录说明
```text
├── README.md 说明文档
├── app       应用目录
│   ├── controllers    控制器目录
│   │   ├── Admin      后台控制器
│   │   │   ├── ErrorController.php
│   │   │   └── IndexController.php
│   │   ├── Api        接口控制器
│   │   └── Base.php   控制器基类
│   ├── libraries      一些常用库
│   │   └── Content.php
│   ├── models         数据库表文件
│   │   └── Article.php
│   ├── providers      数据提供接口
│   │   └── Base.php
│   └── views          暂时用不到
│       └── admin
├── autoloader.php     自动装载文件
├── config             配置文件
│   ├── cache.php
│   ├── cli-routers.php
│   ├── config.php
│   ├── database.php
│   ├── logger.php
│   ├── routes.php
│   ├── session.php
│   └── version.php
├── console            cli脚本，暂时用不到
│   └── crond.php
├── core               框架核心文件
│   ├── App.php        框架入口
│   ├── Cache          缓存
│   │   ├── CacheInterface.php
│   │   ├── Factory.php
│   │   └── File.php
│   ├── Cache.php
│   ├── CacheInterface.php
│   ├── CategoryTree.php
│   ├── Components.php
│   ├── Controller.php
│   ├── Db             数据库
│   │   ├── Factory.php
│   │   ├── Mysql.php
│   │   └── PdoInterface.php
│   ├── Db.php
│   ├── DbInterface.php
│   ├── Di.php         调度器
│   ├── Factory.php
│   ├── FactoryInterface.php
│   ├── Helper.php
│   ├── Loader.php     自动装载器
│   ├── Logger.php     错误处理，未完成
│   ├── Models.php     model
│   ├── Paginator.php  分页处理，未完成
│   ├── Router         路由器
│   │   └── Route.php
│   ├── Router.php
│   └── View.php        未使用
├── public              应用访问入口
│   ├── assets
│   │   ├── css
│   │   ├── fonts
│   │   ├── images
│   │   └── js
│   ├── favicon.ico
│   ├── index.php
│   ├── install
│   ├── robots.txt
│   └── uploads
└── storage            缓存容器
    ├── caches
    │   ├── data
    │   └── views
    └── logs

```

## 配置文件
根目录下的.env是配置文件，目前需要手动配置。可以配置数据库信息、缓存信息等。

## Web服务器配置

### Nginx
在你的站点配置加入下面内容
```text
...

location / {
    try_files $uri $uri/ /index.php?$query_string;
}

...
```
### Apache
1. 打开Apache的`mod_rewrite`模块
2. 在`public`目录下新建`.htaccess`文件
3. 将以下内容写入到`.htaccess`文件中

```text
Options +FollowSymLinks
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
```
