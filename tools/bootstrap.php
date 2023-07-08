<?php

namespace FKSDB\Tools;

use FKSDB\Bootstrap;

require __DIR__ . '/../vendor/autoload.php';
// Load Nette Framework
require __DIR__ . '/../app/Bootstrap.php';

// Configure application
$configurator = Bootstrap::boot();

return $configurator->createContainer();
