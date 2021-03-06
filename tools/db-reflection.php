<?php

use FKSDB\Models\ORM\Columns\ColumnFactory;
use Nette\DI\Container;

const SAFE_LIMIT = 500;

/**
 * @var Container $container
 */
$container = require './bootstrap.php';
set_time_limit(60);

$factories = $container->findByType(ColumnFactory::class);

foreach ($factories as $key => $factoryName) {
    /** @var ColumnFactory $factory */
    $factory = $container->getService($factoryName);
    [, $table, $field] = explode('.', $factoryName);
    echo '| ' . $table . ' | ' . $field . ' | ' . $factory->getPermission()->read . ' | ' . $factory->getPermission()->write . ' |' . "\n";
}

