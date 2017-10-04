<?php

namespace FKSDB\Components\Grids\Brawl;

use BrawlModule\BasePresenter;
use \NiftyGrid\DataSource\NDataSource;
use ORM\Services\Events\ServiceFyziklaniTeam;
use \FKSDB\Components\Grids\BaseGrid;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
class BrawlTeamsGrid extends BaseGrid {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceBrawlTeam;
    /**
     * @var integer
     */
    private $eventID;

    /**
     * BrawlTeamsGrid constructor.
     * @param integer $eventID
     * @param ServiceFyziklaniTeam $serviceBrawlTeam
     */
    public function __construct($eventID, ServiceFyziklaniTeam $serviceBrawlTeam) {
        $this->serviceBrawlTeam = $serviceBrawlTeam;
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
            return $presenter->link(':Brawl:Close:team', [
                'id' => $row->e_fyziklani_team_id,
                'eventID' => $this->eventID
            ]);
        })->setText(_('Uzavřít bodování'))->setShow(function ($row) use ($that) {
            return $that->serviceBrawlTeam->isOpenSubmit($row->e_fyziklani_team_id);
        });
        $teams = $this->serviceBrawlTeam->findParticipating($this->eventID);//->where('points',NULL);
        $this->setDataSource(new NDataSource($teams));
    }
}
