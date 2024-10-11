<?php

declare(strict_types=1);

namespace FKSDB\Components\Game;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use Fykosak\NetteORM\Selection\TypedGroupedSelection;
use Nette\DI\Container;

/**
 * @phpstan-extends BaseGrid<TaskModel,array{}>
 */
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

    protected function configure(): void
    {
        $this->paginate = false;
        $this->addSimpleReferencedColumns([
            '@fyziklani_task.fyziklani_task_id',
            '@fyziklani_task.label',
            '@fyziklani_task.name',
        ]);
    }
}
