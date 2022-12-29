<?php

use Minor\Thread\Contract\Runnable;
use Minor\Thread\Demo\TaskDemo;
use Minor\Thread\Pool\NormalThreadPool;

require_once './vendor/autoload.php';

$threadPool = NormalThreadPool::getInstance('normal', 5);

for ($i = 0; $i < 10; $i++) {
    $threadPool->addTask(new TaskDemo($i));
}
\Minor\Thread\Utils\Timer::start('main');
$threadPool->execute();

$threadPool->waitForResult();

var_dump($threadPool->getResults());
\Minor\Thread\Utils\Timer::end('main');