<?php

namespace FKSDB\Components\Grids\Brawl;

use FKSDB\Components\Grids\BaseGrid;
use ServiceBrawlTask;
use Nette\Database\Table\Selection;
use SQL\SearchableDataSource;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
class BrawlTaskGrid extends BaseGrid {

    /**
     *
     * @var ServiceBrawlTask
     */
    private $serviceBrawlTask;
    /**
     * @var int
     */
    private $eventID;

    public function __construct($eventID, ServiceBrawlTask $serviceBrawlTask) {
        $this->serviceBrawlTask = $serviceBrawlTask;
        $this->eventID = $eventID;
        parent::__construct();
    }

    protected function configure($presenter) {
        parent::configure($presenter);
        $this->addColumn('fyziklani_task_id',_('ID úlohy'));
        $this->addColumn('label',_('#'));
        $this->addColumn('name',_('Název úlohy'));

        $submits = $this->serviceBrawlTask->findAll($this->eventID);
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
