<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\EntityForms\EventFormComponent;
use FKSDB\Components\Grids\Events\EventsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Nette\Security\Resource;

/**
 * @method EventModel getEntity()
 */
class EventPresenter extends BasePresenter
{
    use EntityPresenterTrait;

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
     * @throws ModelNotFoundException
     * @throws GoneException
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

    protected function createComponentGrid(): EventsGrid
    {
        return new EventsGrid($this->getContext(), $this->getSelectedContestYear());
    }

    protected function createComponentCreateForm(): EventFormComponent
    {
        return new EventFormComponent($this->getSelectedContestYear(), $this->getContext(), null);
    }

    /**
     * @throws ModelNotFoundException
     * @throws GoneException
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
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }
}
