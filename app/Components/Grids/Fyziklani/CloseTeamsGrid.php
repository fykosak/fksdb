<?php

namespace FKSDB\Components\Grids\Fyziklani;


use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\ModelEvent;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FyziklaniModule\BasePresenter;
use NiftyGrid\DataSource\NDataSource;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
class CloseTeamsGrid extends BaseGrid {
    /**
     * @var \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * FyziklaniTeamsGrid constructor.
     * @param ModelEvent $event
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam $serviceFyziklaniTeam
     */
    public function __construct(ModelEvent $event, ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->event = $event;
        parent::__construct();
    }
    /**
     * @param  BasePresenter $presenter
     * @throws \NiftyGrid\DuplicateButtonException
     * @throws \NiftyGrid\DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $this->paginate = false;
        $this->addColumn('name', _('Název týmu'));
        $this->addColumn('e_fyziklani_team_id', _('ID týmu'));
        $this->addColumn('points', _('Počet bodů'));

        $this->addColumn('room', _('Místnost'))->setRenderer(function ($row) {
            /**
             * @var \FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam $row
             */
            $position = $row->getPosition();
            if (!$position) {
                return '-';
            }
            $room = $position->getRoom();
            return $room->name;
        });
        $this->addColumn('category', _('Kategorie'));
        $this->addButton('edit', null)->setClass('btn btn-sm btn-success')->setLink(function ($row) use ($presenter) {
            return $presenter->link(':Fyziklani:Close:team', [
                'id' => $row->e_fyziklani_team_id,
                'eventId' => $this->event->event_id
            ]);
        })->setText(_('Uzavřít bodování'))->setShow(function ($row) {
            /**
             * @var ModelFyziklaniTeam $row
             */
            return $row->hasOpenSubmitting();
        });
        $teams = $this->serviceFyziklaniTeam->findParticipating($this->event);//->where('points',NULL);
        $this->setDataSource(new NDataSource($teams));
    }
}
