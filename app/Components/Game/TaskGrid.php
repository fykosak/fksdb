<?php

declare(strict_types=1);

namespace FKSDB\Components\Game;

use FKSDB\Components\Grids\FilterBaseGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\SQL\SearchableDataSource;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\Selection;
use Nette\DI\Container;

class TaskGrid extends FilterBaseGrid
{

    private EventModel $event;

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    protected function getData(): SearchableDataSource
    {
        $dataSource = new SearchableDataSource($this->event->getTasks());
        $dataSource->setFilterCallback(function (Selection $table, array $value) {
            $tokens = preg_split('/\s+/', $value['term']);
            foreach ($tokens as $token) {
                $table->where(
                    'name LIKE CONCAT(\'%\', ? , \'%\') OR fyziklani_task_id LIKE CONCAT(\'%\', ? , \'%\')',
                    $token,
                    $token
                );
            }
        });
        return $dataSource;
    }

    /**
     * @throws BadTypeException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->addColumns(['fyziklani_task.fyziklani_task_id', 'fyziklani_task.label', 'fyziklani_task.name']);
    }
}
