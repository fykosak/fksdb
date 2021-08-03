<?php

declare(strict_types=1);

namespace FKSDB\Components\PDFGenerators\TeamSeating;

use FKSDB\Components\PDFGenerators\Provider\AbstractProviderComponent;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\DI\Container;

abstract class SeatingProviderComponent extends AbstractProviderComponent
{
    protected ModelEvent $event;

    public function __construct(ModelEvent $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }
}
