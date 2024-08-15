<?php

namespace Enna\Chat;

use Workerman\Worker;
use GatewayWorker\BusinessWorker;

require_once __DIR__ . '/../../vendor/autoload.php';

$worker = new BusinessWorker();

$worker->name = 'ChatBusinessWorker';

$worker->count = 4;

$worker->registerAddress = '127.0.0.1:1236';

if (!defined('GLOBAL_START')) {
    Worker::runAll();
}