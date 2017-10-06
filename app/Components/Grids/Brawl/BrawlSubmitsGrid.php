<?php

namespace FKSDB\Components\Grids\Brawl;

use BrawlModule\BasePresenter;
use Nette\Database\Table\Selection;
use ORM\Services\Events\ServiceFyziklaniTeam;
use ServiceBrawlSubmit;
use \FKSDB\Components\Grids\BaseGrid;
use SQL\SearchableDataSource;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
class BrawlSubmitsGrid extends BaseGrid {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceBrawlTeam;
    /**
     *
     * @var ServiceBrawlSubmit
     */
    private $serviceBrawlSubmit;

    /**
     * @var integer
     */
    private $eventID;

    /**
     * BrawlSubmitsGrid constructor.
     * @param integer $eventID
     * @param ServiceBrawlSubmit $serviceBrawlSubmit
     * @param ServiceFyziklaniTeam $serviceBrawlTeam
     */
    public function __construct($eventID, ServiceBrawlSubmit $serviceBrawlSubmit, ServiceFyziklaniTeam $serviceBrawlTeam) {
        $this->serviceBrawlSubmit = $serviceBrawlSubmit;
        $this->serviceBrawlTeam = $serviceBrawlTeam;
        $this->eventID = $eventID;
        parent::__construct();
    }

    /**
     * @param $presenter BasePresenter
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $this->addColumn('name', _('Jméno týmu'));
        $this->addColumn('e_fyziklani_team_id', _('ID týmu'));
        $that = $this;
        $this->addColumn('label', _('Úloha'));
        $this->addColumn('points', _('Body'));
        $this->addColumn('room', _('Místnost'));
        $this->addColumn('modified', _('Zadané'));
        $this->addButton('edit', null)->setClass('btn btn-sm btn-warning')->setLink(function ($row) use ($presenter) {
            return $presenter->link(':Brawl:Submit:edit', ['id' => $row->fyziklani_submit_id]);
        })->setText(_('Upravit'))->setShow(function ($row) use ($that) {
            return $that->serviceBrawlTeam->isOpenSubmit($row->e_fyziklani_team_id) && !is_null($row->points);
        });

        $this->addButton('delete', null)->setClass('btn btn-sm btn-danger')->setLink(function ($row) use ($that) {
            return $that->link("delete!", $row->fyziklani_submit_id);
        })->setConfirmationDialog(function () {
            return _("Opravdu vzít submit úlohy zpět?"); //todo i18n
        })->setText(_('Smazat'))->setShow(function ($row) use ($that) {
            return $that->serviceBrawlTeam->isOpenSubmit($row->e_fyziklani_team_id) && !is_null($row->points);
        });

        $submits = $this->serviceBrawlSubmit->findAll($this->eventID)
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
        $teamID = $this->serviceBrawlSubmit->findByPrimary($id)->e_fyziklani_team_id;
        if (!$teamID) {
            $this->flashMessage(_('Submit neexistuje'), 'danger');
            return;
        }
        if (!$this->serviceBrawlTeam->isOpenSubmit($teamID)) {
            $this->flashMessage('Tento tým má už uzavřené bodování', 'warning');
            return;
        }
        $submit = $this->serviceBrawlSubmit->findByPrimary($id);
        $this->serviceBrawlSubmit->updateModel($submit, [
            'points' => null,
            /* ugly, exclude previous value of `modified` from query
             * so that `modified` is set automatically by DB
             * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
             */
            'modified' => null
        ]);
        $this->serviceBrawlSubmit->save($submit);
        $this->flashMessage(_('Úloha byla smazána.'), 'success');
    }
}
