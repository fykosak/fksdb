<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Grids\BaseGrid;
use ServiceFyziklaniTask;
use Nette\Database\Table\Selection;
use SQL\SearchableDataSource;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
class FyziklaniTaskGrid extends BaseGrid {

    /**
     *
     * @var ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;
    /**
     * @var int
     */
    private $eventID;

    /**
     * FyziklaniTaskGrid constructor.
     * @param integer $eventID
     * @param ServiceFyziklaniTask $serviceFyziklaniTask
     */
    public function __construct($eventID, ServiceFyziklaniTask $serviceFyziklaniTask) {
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->eventID = $eventID;
        parent::__construct();
    }

    protected function configure($presenter) {
        parent::configure($presenter);
        $this->addColumn('fyziklani_task_id',_('ID úlohy'));
        $this->addColumn('label',_('#'));
        $this->addColumn('name',_('Název úlohy'));

        $submits = $this->serviceFyziklaniTask->findAll($this->eventID);
        $dataSource = new SearchableDataSource($submits);
        $dataSource->setFilterCallback(function(Selection $table, $value) {
                    $tokens = preg_split('/\s+/', $value);
                    foreach ($tokens as $token) {
                        $table->where('name LIKE CONCAT(\'%\', ? , \'%\') OR fyziklani_task_id LIKE CONCAT(\'%\', ? , \'%\')', $token, $token);
                    }
                });
        $this->setDataSource($dataSource);
    }
}
