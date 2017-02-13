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
        $this->addColumn('modified', _('Zadané'));
        $this->addButton('edit', null)->setClass('btn btn-xs btn-default')->setLink(function ($row) use ($presenter) {
            return $presenter->link(':Fyziklani:Submit:edit', ['id' => $row->fyziklani_submit_id]);
        })->setText(_('Upravit'))->setShow(function ($row) use ($that) {
            return $that->serviceFyziklaniTeam->isOpenSubmit($row->e_fyziklani_team_id);
        });

        $this->addButton('delete', null)->setClass('btn btn-xs btn-danger')->setLink(function ($row) use ($that) {
            return $that->link("delete!", $row->fyziklani_submit_id);
        })->setConfirmationDialog(function () {
            return _("Opravdu vzít submit úlohy zpět?"); //todo i18n
        })->setText(_('Smazat'))->setShow(function ($row) use ($that) {
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

    public function handleDelete($id) {
        $teamID = $this->serviceFyziklaniSubmit->findByPrimary($id)->e_fyziklani_team_id;
        if (!$teamID) {
            $this->flashMessage(_('Submit neexistuje'), 'danger');
            return;
        }
        if (!$this->serviceFyziklaniTeam->isOpenSubmit($teamID)) {
            $this->flashMessage('Tento tým má už uzavřené bodování', 'warning');
            return;
        }
        try {
            $this->serviceFyziklaniSubmit->getTable()->where('fyziklani_submit_id', $id)->delete();
            $this->flashMessage(_('Úloha byla smazaná'), 'success');
        } catch (Exception $e) {
            $this->flashMessage(_('Vyskytla se chyba'), 'danger');
            \Nette\Diagnostics\Debugger::log($e);
        }
    }
}
