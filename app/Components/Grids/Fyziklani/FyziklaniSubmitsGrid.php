<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FKSDB\Components\Grids\Fyziklani;

use FyziklaniModule\BasePresenter;
use Nette\Diagnostics\Debugger;
use \NiftyGrid\DataSource\NDataSource;
use ServiceFyziklaniSubmit;
use \FKSDB\Components\Grids\BaseGrid;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
class FyziklaniSubmitsGrid extends BaseGrid {
    /**
     * @var BasePresenter
     * @deprecated
     */
    private $presenter;
    /**
     *
     * @var ServiceFyziklaniSubmit
     */
    private $serviceFyziklaniSubmit;
    /**
     * @var integer
     */
    private $eventID;

    public function __construct($eventID, BasePresenter $presenter, ServiceFyziklaniSubmit $serviceFyziklaniSubmit) {

        $this->presenter = $presenter;
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->eventID = $eventID;
        parent::__construct();
    }

    public function isSearchable() {
        return false;
    }

    protected function configure($presenter) {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addColumn('name', _('Jméno týmu'));
        $this->addColumn('e_fyziklani_team_id', _('Team ID'));
        $that = $this;
        $this->addColumn('label', _('Úloha'));
        $this->addColumn('points', _('Body'));
        $this->addColumn('room', _('Místnost'));
        $this->addColumn('submitted_on', _('Zadané'));
        $this->addButton('edit', null)->setClass('btn btn-xs btn-default')->setLink(function ($row) use ($presenter) {
            return $presenter->link(':Fyziklani:Submit:edit', ['id' => $row->fyziklani_submit_id]);
        })->setText(_('Upraviť'))->setShow(function ($row) use ($that, $presenter) {
            return $presenter->isOpenSubmit($row->e_fyziklani_team_id);
        });

        $this->addButton('delete', null)->setClass('btn btn-xs btn-danger')->setLink(function ($row) use ($that) {
            return $that->link("delete!", $row->fyziklani_submit_id);
        })->setConfirmationDialog(function () {
            return _("Opravdu vzít submit úlohy zpět?"); //todo i18n
        })->setText(_('Zmazať'))->setShow(function ($row) use ($that, $presenter) {
            return $presenter->isOpenSubmit($row->e_fyziklani_team_id);
        });

        $submits = $this->serviceFyziklaniSubmit->findAll($this->eventID)->select('fyziklani_submit.*,fyziklani_task.label,e_fyziklani_team_id.name,e_fyziklani_team_id.room');
        $this->setDataSource(new NDataSource($submits));
    }

    public function handleDelete($id) {

        $teamID = $this->presenter->submitToTeam($id);
        if (!$teamID) {
            $this->flashMessage(_('Submit nenexistuje'), 'danger');
            return;
        }
        if (!$this->presenter->isOpenSubmit($teamID)) {
            $this->flashMessage('Tento tým má už uzavreté bodovanie', 'warning');
            return;
        }
        try {
            $this->serviceFyziklaniSubmit->getTable()->where('fyziklani_submit_id', $id)->delete();
            $this->flashMessage(_('Úloha bola zmazaná'), 'success');
        } catch (Exception $e) {
            $this->flashMessage(_('Vykytla sa chyba'), 'danger');
            \Nette\Diagnostics\Debugger::log($e);
        }
    }
}
