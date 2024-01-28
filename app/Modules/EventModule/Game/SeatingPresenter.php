<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Components\TeamSeating\AllPlaces;
use FKSDB\Components\TeamSeating\SeatingForm;
use FKSDB\Components\TeamSeating\Single;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
use Fykosak\Utils\UI\PageTitle;
use Nette\ComponentModel\Container;

final class SeatingPresenter extends BasePresenter
{
    /**
     * @throws EventNotFoundException
     * @throws NotImplementedException
     * @throws UnsupportedLanguageException
     */
    protected function startup(): void
    {
        parent::startup();
        if ($this->getEvent()->event_type_id !== 1) {
            throw new NotImplementedException();
        }
    }

    public function titlePrint(): PageTitle
    {
        return new PageTitle(null, _('Seating - print'), 'fas fa-map-marked-alt');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedPrint(): bool
    {
        return $this->authorizedDefault();
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): bool
    {
        return $this->eventAuthorizator->isAllowed(EventModel::RESOURCE_ID, 'seating', $this->getEvent());
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
        $limit = $this->getParameter('limit', 1000);
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
