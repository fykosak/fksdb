<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use Nette\Database\Table\Selection;
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
     * @param ServiceFyziklaniTask $serviceFyziklaniTask
     */
    public function __construct(ModelEvent $event, ServiceFyziklaniTask $serviceFyziklaniTask) {
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->event = $event;
        parent::__construct();
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
