<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Components\EntityForms\EventFormComponent;
use FKSDB\Components\Grids\Events\EventsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Modules\Core\PresenterTraits\ContestYearEntityTrait;
use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

final class EventPresenter extends BasePresenter
{
    /** @phpstan-use ContestYearEntityTrait<EventModel> */
    use ContestYearEntityTrait;

    private EventService $eventService;

    final public function injectServiceEvent(EventService $eventService): void
    {
        $this->eventService = $eventService;
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(null, _('Events'), 'fas fa-calendar-alt');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(null, _('Add event'), 'fas fa-calendar-plus');
    }

    /**
     * @throws ForbiddenRequestException
     * @throws GoneException
     * @throws ModelNotFoundException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     * @throws \ReflectionException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(null, sprintf(_('Edit event %s'), $this->getEntity()->name), 'fas fa-calendar-day');
    }

    /**
     * @throws NotImplementedException
     */
    public function actionDelete(): void
    {
        throw new NotImplementedException();
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
     * @throws ModelNotFoundException
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     * @throws \ReflectionException
     */
    protected function createComponentEditForm(): EventFormComponent
    {
        return new EventFormComponent($this->getSelectedContestYear(), $this->getContext(), $this->getEntity());
    }

    protected function getORMService(): EventService
    {
        return $this->eventService;
    }

    /**
     * @param Resource|string|null $resource
     * @throws NoContestAvailable
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }
}
