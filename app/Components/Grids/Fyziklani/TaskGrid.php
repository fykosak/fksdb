<?php

declare(strict_types=1);

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\SQL\SearchableDataSource;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DuplicateColumnException;

class TaskGrid extends BaseGrid
{

    private EventModel $event;

    public function __construct(EventModel $event, Container $container)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    protected function getData(): IDataSource
    {
        $submits = $this->event->getFyziklaniTasks();
        $dataSource = new SearchableDataSource($submits);
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
     * @throws DuplicateColumnException
     */
    protected function configure(Presenter $presenter): void
    {
        parent::configure($presenter);
        $this->addColumn('fyziklani_task_id', _('Task Id'));
        $this->addColumn('label', _('#'));
        $this->addColumn('name', _('Task name'));
    }
}
