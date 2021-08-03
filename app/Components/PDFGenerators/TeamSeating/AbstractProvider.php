<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating;

use FKSDB\Components\PDFGenerators\Provider\Provider;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\DI\Container;

abstract class AbstractProvider implements Provider
{
    protected Container $container;
    protected ModelEvent $event;

    public function __construct(ModelEvent $event, Container $container)
    {
        $this->event = $event;
        $this->container = $container;
    }
}
