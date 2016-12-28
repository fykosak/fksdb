<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FKSDB\Components\Grids\Fyziklani;

use FyziklaniModule\ClosePresenter;
use \NiftyGrid\DataSource\NDataSource;

/**
 * Description of SubmitsGrid
 *
 * @author miso
 */
class FyziklaniTeamsGrid extends \FKSDB\Components\Grids\BaseGrid {

    private $database;

    public function __construct(\Nette\Database\Connection $database) {
        $this->database = $database;
        parent::__construct();
    }

//    public function isSearchable() {
//        return $this->searchable;
//    }
//
//    public function setSearchable($searchable) {
//        $this->searchable = $searchable;
//    }

    protected function configure($presenter) {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addColumn('name',_('Názov týmu'));
        $this->addColumn('e_fyziklani_team_id',_('Tým ID'));


        //$this->addColumn('points',_('Počet bodů'));
        $this->addColumn('room',_('Místnost'));
        $this->addColumn('category',_('Kategória'));

        $this->addButton('edit',null)
                ->setClass('btn btn-xs btn-success')
                ->setLink(function($row)use($presenter) {
                    return $presenter->link(':Fyziklani:Close:team',['id' => $row->e_fyziklani_team_id,'eventID'=>$presenter->eventID]);
                })
                ->setText(_('Uzavrieť bodovanie'));
        $teams = $this->database->table('e_fyziklani_team')->select('*')->where('event_id',$presenter->eventID)->where('status?','participated')->where('points',NULL);
        $this->setDataSource(new NDataSource($teams));
    }
}
