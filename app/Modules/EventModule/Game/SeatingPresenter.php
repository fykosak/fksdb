<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Components\TeamSeating\AllPlaces;
use FKSDB\Components\TeamSeating\Single;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
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
        return new PageTitle(null, _('Print'), 'fas fa-map-marked-alt');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedPrint(): bool
    {
        return $this->authorizedList();
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of rooms'), 'fas fa-print');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedList(): bool
    {
        return $this->isAllowed('game.seating', 'default');
    }

    public function titlePreview(): PageTitle
    {
        return new PageTitle(null, _('Preview'), 'fas fa-search');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedPreview(): bool
    {
        return $this->authorizedList();
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentTeamList(): Container
    {
        $limit = $this->getParameter('limit', 1000);
        $offset = $this->getParameter('offset', 0);
        /** @phpstan-var \Iterator<TeamModel2> $teams */ // TODO!!!!
        $teams = $this->getEvent()->getTeams()->limit((int)$limit, (int)$offset);
        $container = new Container();
        foreach ($teams as $team) {
            $container->addComponent(new Single($this->getContext(), $team), 'team' . $team->fyziklani_team_id);
        }
        return $container;
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentSeatingPreview(): AllPlaces
    {
        return new AllPlaces($this->getContext(), $this->getEvent());
    }
}
