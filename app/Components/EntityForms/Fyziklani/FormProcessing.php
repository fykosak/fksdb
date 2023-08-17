<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use FKSDB\Models\ORM\Models\EventModel;
use Nette\DI\Container;
use Nette\Forms\Form;

abstract class FormProcessing
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $container->callInjects($this);
    }

    /**
     * @phpstan-param array{team:array{category:string,force_a:bool,name:string}} $values
     * @phpstan-return array{team:array{category:string,force_a:bool,name:string}}
     */
    abstract public function __invoke(array $values, Form $form, EventModel $event): array;
}
