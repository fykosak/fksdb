<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\EntityForms\EventFormComponent;
use FKSDB\Components\Grids\Events\EventsGrid;
use FKSDB\Models\Authorization\Resource\ContestYearResourceHolder;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Modules\Core\PresenterTraits\ContestYearEntityTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;

final class EventPresenter extends BasePresenter
{
    /** @phpstan-use ContestYearEntityTrait<EventModel> */
    use ContestYearEntityTrait;

    private EventService $eventService;

    final public function injectServiceEvent(EventService $eventService): void
    {
        $this->eventService = $eventService;
    }

    /**
     * @throws NoContestYearAvailable
     * @throws NoContestAvailable
     */
    public function authorizedList(): bool
    {
        return $this->authorizator->isAllowedContestYear(
            ContestYearResourceHolder::fromResourceId(EventModel::RESOURCE_ID, $this->getSelectedContestYear()),
            'list',
            $this->getSelectedContestYear()
        );
    }
    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Events'), 'fas fa-calendar-alt');
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    public function authorizedCreate(): bool
    {
        return $this->authorizator->isAllowedContestYear(
            ContestYearResourceHolder::fromResourceId(EventModel::RESOURCE_ID, $this->getSelectedContestYear()),
            'create',
            $this->getSelectedContestYear()
        );
    }
    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Add event'), 'fas fa-calendar-plus');
    }

    /**
     * @throws NotFoundException
     * @throws GoneException
     * @throws NoContestYearAvailable
     * @throws NoContestAvailable
     * @throws \ReflectionException
     * @throws ForbiddenRequestException
     */
    public function authorizedEdit(): bool
    {
        return $this->authorizator->isAllowedContestYear(
            ContestYearResourceHolder::fromOwnResource($this->getEntity()),
            'edit',
            $this->getSelectedContestYear()
        );
    }
    /**
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     * @throws \ReflectionException
     * @throws NotFoundException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, sprintf(_('Edit event %s'), $this->getEntity()->name), 'fas fa-calendar-day');
    }

    /**
     * @throws NoContestYearAvailable
     * @throws NoContestAvailable
     */
    protected function createComponentGrid(): EventsGrid
    {
        return new EventsGrid($this->getContext(), $this->getSelectedContestYear());
    }

    /**
     * @throws NoContestYearAvailable
     * @throws NoContestAvailable
     */
    protected function createComponentCreateForm(): EventFormComponent
    {
        return new EventFormComponent($this->getSelectedContestYear(), $this->getContext(), null);
    }

    /**
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     * @throws \ReflectionException
     * @throws NotFoundException
     */
    protected function createComponentEditForm(): EventFormComponent
    {
        return new EventFormComponent($this->getSelectedContestYear(), $this->getContext(), $this->getEntity());
    }

    protected function getORMService(): EventService
    {
        return $this->eventService;
    }
}
