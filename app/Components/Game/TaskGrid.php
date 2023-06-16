<?php

declare(strict_types=1);

namespace FKSDB\Components\Game;

use FKSDB\Components\Grids\Components\Grid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\DI\Container;

class TaskGrid extends Grid
{
    private EventModel $event;

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    protected function getModels(): TypedGroupedSelection
    {
        return $this->event->getTasks();
    }

    /**
     * @throws BadTypeException
     * @throws \ReflectionException
     */
    protected function configure(): void
    {
        $this->addColumns(['fyziklani_task.fyziklani_task_id', 'fyziklani_task.label', 'fyziklani_task.name']);
    }
}
