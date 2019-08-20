<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Forms\Factories\TableReflectionFactory;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FyziklaniModule\BasePresenter;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
class CloseTeamsGrid extends BaseGrid {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * FyziklaniTeamsGrid constructor.
     * @param ModelEvent $event
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @param TableReflectionFactory $tableReflectionFactory
     */
    public function __construct(ModelEvent $event, ServiceFyziklaniTeam $serviceFyziklaniTeam, TableReflectionFactory $tableReflectionFactory) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->event = $event;
        parent::__construct($tableReflectionFactory);
    }

    /**
     * @param BasePresenter $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $this->paginate = false;

        $this->addReflectionColumn('e_fyziklani_team', 'name', ModelFyziklaniTeam::class);
        $this->addReflectionColumn('e_fyziklani_team', 'e_fyziklani_team_id', ModelFyziklaniTeam::class);
        $this->addReflectionColumn('e_fyziklani_team', 'points', ModelFyziklaniTeam::class);
        $this->addReflectionColumn('e_fyziklani_team', 'category', ModelFyziklaniTeam::class);

        $this->addButton('edit', null)->setClass('btn btn-sm btn-success')->setLink(function ($row) use ($presenter) {
            return $presenter->link(':Fyziklani:Close:team', [
                'id' => $row->e_fyziklani_team_id,
                'eventId' => $this->event->event_id
            ]);
        })->setText(_('Close submitting'))->setShow(function ($row) {
            /**
             * @var ModelFyziklaniTeam $row
             */
            return $row->hasOpenSubmitting();
        });
        $teams = $this->serviceFyziklaniTeam->findParticipating($this->event);//->where('points',NULL);
        $this->setDataSource(new NDataSource($teams));
    }
}
