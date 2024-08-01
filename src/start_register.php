<?php

namespace Enna\Chat;

use \Workerman\Worker;
use \GatewayWorker\Register;

require_once __DIR__ . '/../vendor/autoload.php';

// register服务必须是text协议，监听地址请用内网ip或者127.0.0.1
// 为了安全，register不能监听0.0.0.0，也就是register服务不能暴露给外网
$register = new Register('text://127.0.0.1:1236');


if (!defined('GLOBAL_START')) {
    Worker::runAll();
}