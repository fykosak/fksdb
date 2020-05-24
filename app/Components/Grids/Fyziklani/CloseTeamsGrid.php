<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FyziklaniModule\BasePresenter;
use Nette\DI\Container;
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
     * @param Container $container
     */
    public function __construct(ModelEvent $event, Container $container) {
        parent::__construct($container);
        $this->event = $event;
    }

    /**
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @return void
     */
    public function injectServiceFyziklaniTeam(ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    /**
     * @param BasePresenter $presenter
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     * @throws NotImplementedException
     */
    protected function configure($presenter) {
        parent::configure($presenter);

        $this->paginate = false;

        $this->addColumns([
            DbNames::TAB_E_FYZIKLANI_TEAM . '.name',
            DbNames::TAB_E_FYZIKLANI_TEAM . '.e_fyziklani_team_id',
            DbNames::TAB_E_FYZIKLANI_TEAM . '.points',
            DbNames::TAB_E_FYZIKLANI_TEAM . '.category',
            DbNames::TAB_E_FYZIKLANI_TEAM . '.opened_submitting'
        ]);
        $this->addColumn('room', _('Room'))->setRenderer(function (ModelFyziklaniTeam $row) {
            $position = $row->getPosition();
            if (is_null($position)) {
                return NotSetBadge::getHtml();
            }
            return $position->getRoom()->name;
        });
        $this->addLinkButton(':Fyziklani:Close:team', 'close', _('Close submitting'), false, [
            'id' => 'e_fyziklani_team_id',
            'eventId' => 'event_id',
        ])->setShow(function (ModelFyziklaniTeam $row) {
            return $row->isReadyForClosing();
        });
        $teams = $this->serviceFyziklaniTeam->findParticipating($this->event);//->where('points',NULL);
        $this->setDataSource(new NDataSource($teams));
    }

    protected function getModelClassName(): string {
        return ModelFyziklaniTeam::class;
    }
}
