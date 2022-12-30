<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Game;

use FKSDB\Components\Grids\Components\Grid;
use FKSDB\Components\PDFGenerators\Providers\ProviderComponent;
use FKSDB\Components\PDFGenerators\TeamSeating\SingleTeam\PageComponent;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\Fyziklani\Seating\RoomModel;
use FKSDB\Models\ORM\Services\Fyziklani\Seating\RoomService;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Fykosak\NetteORM\Service;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

/**
 * @method RoomModel getEntity(bool $throw = true)
 */
class SeatingPresenter extends BasePresenter
{
    use EntityPresenterTrait;

    private RoomService $roomService;

    /**
     * @throws EventNotFoundException
     * @throws NotImplementedException
     * @throws ForbiddenRequestException
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
        return new PageTitle(null, _('Print'), 'fa fa-map-marked-alt');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('List of rooms'), 'fa fa-print');
    }

    public function titlePreview(): PageTitle
    {
        return new PageTitle(null, _('Preview'), 'fa fa-search');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedPreview(): void
    {
        $this->authorizedList();
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedPrint(): void
    {
        $this->authorizedList();
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedList(): void
    {
        $this->setAuthorized($this->isAllowed('game.seating', 'default'));
    }

    final public function renderList(): void
    {
        $this->template->rooms = $this->roomService->getTable();
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentSeatingList(): ProviderComponent
    {
        $limit = $this->getParameter('limit', 1000);
        $offset = $this->getParameter('offset', 0);
        return new ProviderComponent(
            new PageComponent($this->getContext()),
            $this->getEvent()->getTeams()->limit((int)$limit, (int)$offset),
            $this->getContext()
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws ModelNotFoundException
     * @throws GoneException
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

    protected function getORMService(): Service
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
     * @throws GoneException
     */
    protected function createComponentGrid(): Grid
    {
        throw new GoneException();
    }
}
