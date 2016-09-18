<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FKSDB\Components\Grids\Fyziklani;

use \NiftyGrid\DataSource\NDataSource;

/**
 * Description of SubmitsGrid
 *
 * @author miso
 */
class FyziklaniSubmitsGrid extends \FKSDB\Components\Grids\BaseGrid {

    private $database;
    protected $searchable;

    public function __construct(\Nette\Database\Connection $database) {
        $this->database = $database;
        parent::__construct();
    }

    public function isSearchable() {
        return $this->searchable;
    }

    public function setSearchable($searchable) {
        $this->searchable = $searchable;
    }

    protected function configure($presenter) {
        parent::configure($presenter);
        $this->addColumn('name',_('Názov týmu'));
        $this->addColumn('e_fyziklani_team_id',_('Tým ID'));
        $that = $this;
        $this->addColumn('label',_('Úloha'));
        $this->addColumn('points',_('počet bodů'));
        $this->addButton('edit',null)
                ->setClass('btn btn-xs btn-default')
                ->setLink(function($row)use($presenter) {
                    return $presenter->link(':Org:Fyziklani:edit',['id' => $row->fyziklani_submit_id]);
                })
                ->setText(_('Upraviť'));

        $this->addButton('delete',null)
                ->setClass('btn btn-xs btn-danger')
                ->setLink(function($row) use ($that) {
                    return $that->link("delete!",$row->fyziklani_submit_id);
                })
                ->setConfirmationDialog(function() {
                    return _("Opravdu vzít submit úlohy zpět?"); //todo i18n
                })
                ->setText(_('Zmazať'));
        $submits = $this->database->table('fyziklani_submit')->select('fyziklani_submit.*,fyziklani_task.label,e_fyziklani_team_id.name')->where('e_fyziklani_team_id.event_id = ?',$presenter->getCurrentEventID(null));
        $this->setDataSource(new NDataSource($submits));
    }

    public function handleDelete($id) {
        if($this->database->queryArgs('DELETE from fyziklani_submit WHERE fyziklani_submit_id=?',[$id])){
            $this->flashMessage('Úloha bola zmazaná','success');
            $this->redirect('this');
        }else{
            $this->flashMessage('Vykytla sa chyba','danger');
        }
    }

}
