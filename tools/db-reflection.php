<?php

use FKSDB\Components\DatabaseReflection\ColumnFactories\IColumnFactory;
use Nette\DI\Container;

const SAFE_LIMIT = 500;

/**
 * @var Container $container
 */
$container = require './bootstrap.php';
set_time_limit(60);

$factories = $container->findByType(IColumnFactory::class);

foreach ($factories as $key => $factoryName) {
    /** @var IColumnFactory $factory */
    $factory = $container->getService($factoryName);
    list(, $table, $field) = explode('.', $factoryName);
    echo '| ' . $table . ' | ' . $field . ' | ' . $factory->getPermission()->read . ' | ' . $factory->getPermission()->write . ' |' . "\n";
}

