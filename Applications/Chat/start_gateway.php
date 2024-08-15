<?php

namespace Enna\Chat;

use Workerman\Worker;
use GatewayWorker\Gateway;

require_once __DIR__ . '/../../vendor/autoload.php';

$gateway = new Gateway("websocket://0.0.0.0:7272");

$gateway->name = 'ChatGateway';

$gateway->count = 2;

$gateway->lanIp = '127.0.0.1';

$gateway->startPort = 2300;

$gateway->pingInterval = 10;

$gateway->pingData = '{"type":"ping"}';

$gateway->registerAddress = '127.0.0.1:1236';

if (!defined('GLOBAL_START')) {
    Worker::runAll();
}

