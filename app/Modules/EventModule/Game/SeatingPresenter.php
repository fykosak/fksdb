<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Components\Game\Seating\AllPlaces;
use FKSDB\Components\Game\Seating\SeatingForm;
use FKSDB\Components\Game\Seating\Single;
use FKSDB\Models\Authorization\Resource\EventResourceHolder;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\Utils\UI\PageTitle;
use Nette\ComponentModel\Container;

final class SeatingPresenter extends BasePresenter
{
    protected function isEnabled(): bool
    {
        return $this->getEvent()->event_type_id === 1;
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedPrint(): bool
    {
        return $this->authorizedDefault();
    }

    public function titlePrint(): PageTitle
    {
        return new PageTitle(null, _('Seating - print'), 'fas fa-map-marked-alt');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): bool
    {
        return $this->authorizator->isAllowedEvent(EventResourceHolder::fromOwnResource($this->getEvent()), 'seating', $this->getEvent());
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Seating'), 'fas fa-search');
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentTeamList(): Container
    {
        $limit = $this->getParameter('limit', 500);
        $offset = $this->getParameter('offset', 0);
        $teams = $this->getEvent()->getTeams()->limit((int)$limit, (int)$offset);
        $container = new Container();
        /** @var TeamModel2 $team */
        foreach ($teams as $team) {
            $container->addComponent(new Single($this->getContext(), $team), 'team' . $team->fyziklani_team_id);
        }
        return $container;
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentPreview(): AllPlaces
    {
        return new AllPlaces($this->getContext(), $this->getEvent());
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentForm(): SeatingForm
    {
        return new SeatingForm($this->getContext(), $this->getEvent());
    }
}
