<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FyziklaniModule\BasePresenter;
use \NiftyGrid\DataSource\NDataSource;
use ORM\Services\Events\ServiceFyziklaniTeam;
use \FKSDB\Components\Grids\BaseGrid;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
class FyziklaniTeamsGrid extends BaseGrid {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;
    /**
     * @var integer
     */
    private $eventID;

    /**
     * FyziklaniTeamsGrid constructor.
     * @param integer $eventID
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     */
    public function __construct($eventID, ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->eventID = $eventID;
        parent::__construct();
    }

//    public function isSearchable() {
//        return $this->searchable;
//    }
//
//    public function setSearchable($searchable) {
//        $this->searchable = $searchable;
//    }
    /**
     * @param $presenter BasePresenter
     */
    protected function configure($presenter) {
        parent::configure($presenter);
        $this->paginate = false;
        $this->addColumn('name', _('Název týmu'));
        $this->addColumn('e_fyziklani_team_id', _('ID týmu'));
        $this->addColumn('points', _('Počet bodů'));
        $this->addColumn('room', _('Místnost'));
        $this->addColumn('category', _('Kategorie'));
        $that = $this;
        $this->addButton('edit', null)->setClass('btn btn-xs btn-success')->setLink(function ($row) use ($presenter) {
            return $presenter->link(':Fyziklani:Close:team', [
                'id' => $row->e_fyziklani_team_id,
                'eventID' => $this->eventID
            ]);
        })->setText(_('Uzavřít bodování'))->setShow(function ($row) use ($that) {
            return $that->serviceFyziklaniTeam->isOpenSubmit($row->e_fyziklani_team_id);
        });
        $teams = $this->serviceFyziklaniTeam->findParticipating($this->eventID);//->where('points',NULL);
        $this->setDataSource(new NDataSource($teams));
    }
}
