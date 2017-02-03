<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FyziklaniModule\BasePresenter;
use Nette\Database\Table\Selection;
use Nette\Diagnostics\Debugger;
use ORM\Services\Events\ServiceFyziklaniTeam;
use ServiceFyziklaniSubmit;
use \FKSDB\Components\Grids\BaseGrid;
use SQL\SearchableDataSource;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
class FyziklaniSubmitsGrid extends BaseGrid {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;
    /**
     *
     * @var ServiceFyziklaniSubmit
     */
    private $serviceFyziklaniSubmit;

    /**
     * @var integer
     */
    private $eventID;

    public function __construct($eventID, ServiceFyziklaniSubmit $serviceFyziklaniSubmit, ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->eventID = $eventID;
        parent::__construct();
    }

    protected function configure($presenter) {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addColumn('name', _('Jméno týmu'));
        $this->addColumn('e_fyziklani_team_id', _('ID týmu'));
        $that = $this;
        $this->addColumn('label', _('Úloha'));
        $this->addColumn('points', _('Body'));
        $this->addColumn('room', _('Místnost'));
        //$this->addColumn('', _('Zadané'));
        $this->addButton('edit', null)->setClass('btn btn-xs btn-default')->setLink(function ($row) use ($presenter) {
            return $presenter->link(':Fyziklani:Submit:edit', ['id' => $row->fyziklani_submit_id]);
        })->setText(_('Upravit'))->setShow(function ($row) use ($that) {
            return $that->serviceFyziklaniTeam->isOpenSubmit($row->e_fyziklani_team_id);
        });

        $submits = $this->serviceFyziklaniSubmit->findAll($this->eventID)
            ->select('fyziklani_submit.*,fyziklani_task.label,e_fyziklani_team_id.name,e_fyziklani_team_id.room');
        $dataSource = new SearchableDataSource($submits);
        $dataSource->setFilterCallback(function (Selection $table, $value) {
            $tokens = preg_split('/\s+/', $value);
            foreach ($tokens as $token) {
                $table->where('e_fyziklani_team_id.name LIKE CONCAT(\'%\', ? , \'%\') OR fyziklani_task.label LIKE CONCAT(\'%\', ? , \'%\')', $token, $token);
            }
        });
        $this->setDataSource($dataSource);
    }
}
