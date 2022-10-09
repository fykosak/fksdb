<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\FormAdjustment;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use Nette\DI\Container;
use Nette\Forms\Form;

abstract class FormAdjustment
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $container->callInjects($this);
    }

    abstract public function __invoke(array $values, Form $form, EventModel $event, ModelHolder $holder): void;
}
