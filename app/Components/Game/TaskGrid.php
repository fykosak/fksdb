<?php

declare(strict_types=1);

namespace FKSDB\Components\Game;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use Fykosak\NetteORM\TypedGroupedSelection;
use Nette\DI\Container;

class TaskGrid extends BaseGrid
{
    private EventModel $event;

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    /**
     * @phpstan-return TypedGroupedSelection<TaskModel>
     */
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
