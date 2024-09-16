<?php

declare(strict_types=1);

use Nette\Application\UI\Presenter;
use Nette\DI\Container;

const SAFE_LIMIT = 500;

/** @var Container $container */
$container = require __DIR__ . '/bootstrap.php';
$services = $container->findByType(Presenter::class);
$data = [];
foreach ($services as $presenter) {
    $datum = [];
    $reflection = new ReflectionClass($container->getService($presenter));
    $methods = $reflection->getMethods();
    foreach ($methods as $method) {
        $methodName = $method->getName();
        if (preg_match('/authorized([A-Za-z]+)/', $methodName, $matches)) {
            $datum[$matches[1]]['auth'] = true;
        }
        if (preg_match('/title([A-Za-z]+)/', $methodName, $matches)) {
            $datum[$matches[1]]['title'] = true;
        }
        if (preg_match('/action([A-Za-z]+)/', $methodName, $matches)) {
            $datum[$matches[1]]['action'] = true;
        }
        if (preg_match('/render([A-Za-z]+)/', $methodName, $matches)) {
            $datum[$matches[1]]['render'] = true;
        }
    }
    foreach ($datum as $action => $methods) {
        if (isset($methods['auth']) xor isset($methods['title'])) {
            echo $reflection->getName() . ' -> ' . $action . "\n";
        }
    }
    $data[$reflection->getName()] = $datum;
}

file_put_contents('r.json', json_encode($data));


