<?php
/**
 * run with command
 * php start.php start
 */

ini_set('display_errors', 'on');

use Workerman\Worker;

if (strpos(strtolower(PHP_OS), 'win') === 0) {
    exit("start.php not support windows, please use start_for_win.bat\n");
}

if (!extension_loaded('pcntl')) {
    exit("Please install pcntl extension.");
}

if (!extension_loaded('posix')) {
    exit("Please install posix extension.");
}

define('GLOBAL_START', 1);

require_once __DIR__ . '/vendor/autoload.php';

foreach (glob(__DIR__ . '/src/*/start*.php') as $start_file) {
    require_once $start_file;
}

Worker::runAll();
