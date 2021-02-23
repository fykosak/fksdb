<?php

namespace FKSDB\Components\Grids\Fyziklani;

use FKSDB\Components\Controls\Badges\NotSetBadge;
use FKSDB\Components\Grids\BaseGrid;
use FKSDB\Components\Grids\EntityGrid;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Application\IPresenter;
use Nette\DI\Container;
use NiftyGrid\DataSource\IDataSource;
use NiftyGrid\DataSource\NDataSource;
use NiftyGrid\DuplicateButtonException;
use NiftyGrid\DuplicateColumnException;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 * @property ServiceFyziklaniTeam $service
 */
class CloseTeamsGrid extends EntityGrid {

    private ModelEvent $event;

    public function __construct(ModelEvent $event, Container $container) {
        parent::__construct($container,ServiceFyziklaniTeam::class,[
            'e_fyziklani_team.name',
            'e_fyziklani_team.e_fyziklani_team_id',
            'e_fyziklani_team.points',
            'e_fyziklani_team.category',
            'e_fyziklani_team.opened_submitting',
        ],[]);
        $this->event = $event;
    }

    protected function getData(): IDataSource {
        $teams = $this->service->findParticipating($this->event);//->where('points',NULL);
        return new NDataSource($teams);
    }

    /**
     * @param IPresenter $presenter
     * @return void
     * @throws BadTypeException
     * @throws DuplicateButtonException
     * @throws DuplicateColumnException
     */
    protected function configure(IPresenter $presenter): void {
        parent::configure($presenter);

        $this->paginate = false;

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
        ])->setShow(function (ModelFyziklaniTeam $row): bool {
            return $row->canClose(false);
        });
    }

    protected function getModelClassName(): string {
        return ModelFyziklaniTeam::class;
    }
}
