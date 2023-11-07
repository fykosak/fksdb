<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani\Processing;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
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
     * @phpstan-param array{team:array{category:string,name:string}} $values
     * @phpstan-return array{team:array{category:string,name:string}}
     */
    abstract public function __invoke(array $values, Form $form, EventModel $event, ?TeamModel2 $model): array;
}
