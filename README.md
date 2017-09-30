# static7_preview
static7基础管理系统 预览版本  

# 由于tp5.1还处于RC版本 系统功能可能处于不稳定状态,仅提供学习交流.



运行环境 
===============

>  static7_preview预览版的运行环境要求 PHP7.1.0  以上。

> 建议通过虚拟域名访问

> mysql 建议关闭严格模式
 
window系统 WampServer Version 3.0.6 64bit 配置示例

配置如下通过虚拟域名访问

配置apache下的httpd-vhosts.conf文件 路径X:\wamp64\bin\apache\apache2.4.23\conf\extra

增加以下代码
~~~
<VirtualHost *:80>
    DocumentRoot "X:/xxx/tp5/public/"
    ServerName www.tp5.com
    ErrorLog "logs/dummy-host.example.com-error.log"
    CustomLog "logs/dummy-host.example.com-access.log" common
    <Directory "X:/xxx/tp5/public/">
    Options +Indexes +FollowSymLinks +MultiViews
    AllowOverride all
    Require all granted
</Directory>
</VirtualHost>
~~~
然后重启wamp

再打开自己本地的C:\Windows\System32\drivers\etchosts文件,配置如下：
~~~
127.0.0.1 www.tp5.com
~~~

关闭mysql 的严格模式

修改my.ini

sql-mode="STRICT_TRANS_TABLES,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"

修改为

sql-mode="NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION"   


# 访问

内置安装模块 先安装,然后在访问后台  

安装URL:www.xxx.com/install/index/index.html  

后台url:www.xxx.com/admin/login/index.html