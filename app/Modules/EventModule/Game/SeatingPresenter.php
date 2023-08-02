<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Components\Grids\Components\BaseGrid;
use FKSDB\Components\PDFGenerators\Providers\ProviderComponent;
use FKSDB\Components\PDFGenerators\TeamSeating\SingleTeam\PageComponent;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\Fyziklani\Seating\RoomModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\Seating\RoomService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

final class SeatingPresenter extends BasePresenter
{
    /** @use EntityPresenterTrait<RoomModel> */
    use EntityPresenterTrait;

    private RoomService $roomService;

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

    public function injectRoomService(RoomService $roomService): void
    {
        $this->roomService = $roomService;
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

    final public function renderList(): void
    {
        $this->template->rooms = $this->roomService->getTable();
    }

    /**
     * @throws EventNotFoundException
     * @phpstan-return ProviderComponent<TeamModel2>
     */
    protected function createComponentSeatingList(): ProviderComponent
    {
        $limit = $this->getParameter('limit', 1000);
        $offset = $this->getParameter('offset', 0);
        /** @phpstan-var \Iterator<TeamModel2> $teams */ // TODO!!!!
        $teams = $this->getEvent()->getTeams()->limit((int)$limit, (int)$offset);
        return new ProviderComponent(
            new PageComponent($this->getContext()),
            $teams,
            $this->getContext()
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws ModelNotFoundException
     * @throws GoneException
     * @phpstan-return ProviderComponent<null>
     */
    protected function createComponentSeatingPreview(): ProviderComponent
    {
        return new ProviderComponent(
            new \FKSDB\Components\PDFGenerators\TeamSeating\AllTeams\PageComponent(
                $this->getEvent(),
                $this->getEntity(),
                $this->getContext()
            ),
            [null],
            $this->getContext()
        );
    }

    /**
     * @param Resource|string|null $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isAllowed($resource, $privilege);
    }

    protected function getORMService(): RoomService
    {
        return $this->roomService;
    }

    /**
     * @throws GoneException
     */
    protected function createComponentCreateForm(): Control
    {
        throw new GoneException();
    }

    /**
     * @throws GoneException
     */
    protected function createComponentEditForm(): Control
    {
        throw new GoneException();
    }

    /**
     * @return never
     * @throws GoneException
     */
    protected function createComponentGrid(): BaseGrid
    {
        throw new GoneException();
    }
}
