# enna-chat
=======
A chat demo based on workerman

特性
======
* 使用websocket协议
* 多浏览器支持
* 多房间支持
* 私聊支持
* 掉线自动重连
* 支持多服务器部署
* 业务逻辑全部在一个文件中

下载安装
======
1、git clone https://github.com/enna7029/enna-chat.git

2、composer install



启动停止(Linux系统)
=====
以debug方式启动  
```php start.php start  ```

以daemon方式启动  
```php start.php start -d ```

启动(windows系统)
======
双击start_for_win.bat


启动(Linux系统或windows系统)
======
启动web进程:php src\start_web.php
启动register监听进程:php src\start_register.php
启动websocket监听进程:php src\gateway.php
启动业务处理进程:php src\start_businessworker.php

测试
======
浏览器访问 http://服务器ip或域:55151,例如http://127.0.0.1:55151