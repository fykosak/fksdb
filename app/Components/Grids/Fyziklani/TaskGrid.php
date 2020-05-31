<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use NiftyGrid\DuplicateColumnException;
use SQL\SearchableDataSource;

/**
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
class TaskGrid extends BaseGrid {

    /**
     * @var ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * FyziklaniTaskGrid constructor.
     * @param ModelEvent $event
     * @param Container $container
     */
    public function __construct(ModelEvent $event, Container $container) {
        parent::__construct($container);
        $this->event = $event;
    }

    /**
     * @param ServiceFyziklaniTask $serviceFyziklaniTask
     * @return void
     */
    public function injectServiceFyziklaniTask(ServiceFyziklaniTask $serviceFyziklaniTask) {
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
    }

    /**
     * @param $presenter
     * @throws DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $this->addColumn('fyziklani_task_id', _('Task Id'));
        $this->addColumn('label', _('#'));
        $this->addColumn('name', _('Task name'));

        $submits = $this->serviceFyziklaniTask->findAll($this->event);
        $dataSource = new SearchableDataSource($submits);
        $dataSource->setFilterCallback(function (Selection $table, $value) {
            $tokens = preg_split('/\s+/', $value);
            foreach ($tokens as $token) {
                $table->where('name LIKE CONCAT(\'%\', ? , \'%\') OR fyziklani_task_id LIKE CONCAT(\'%\', ? , \'%\')', $token, $token);
            }
        });
        $this->setDataSource($dataSource);
    }
}
