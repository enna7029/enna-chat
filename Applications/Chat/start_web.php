<?php

use Workerman\Worker;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use Workerman\Connection\TcpConnection;

require_once __DIR__ . '/../../vendor/autoload.php';

$web = new Worker("http://0.0.0.0:55151");

$web->count = 2;

define('WEBROOT', __DIR__ . DIRECTORY_SEPARATOR . 'Web');

$web->onMessage = function (TcpConnection $connection, Request $request) {
    $path = $request->path();

    //情况1:
    if ($path === '/') {
        $connection->send(exec_php_file(WEBROOT . '/index.php'));
        return;
    }

    $file = realpath(WEBROOT . $path);

    //情况2
    if ($file === false) {
        $connection->send(new Response(404, [], '<h3>404 Not Found</h3>'));
        return;
    }

    //情况3:
    if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        $connection->send(exec_php_file($file));
        return;
    }
};

function exec_php_file($file)
{
    ob_start();
    try {
        include $file;
    } catch (\Exception $e) {
        echo $e;
    }
    return ob_get_clean();
}

if (!defined('GLOBAL_START')) {
    Worker::runAll();
}