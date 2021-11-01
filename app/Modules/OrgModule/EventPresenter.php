<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\EntityForms\EventFormComponent;
use FKSDB\Components\Grids\Events\EventsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\ServiceEvent;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use Nette\Security\Resource;

/**
 * @method ModelEvent getEntity()
 */
class EventPresenter extends BasePresenter
{
    use EntityPresenterTrait;

    private ServiceEvent $serviceEvent;

    final public function injectServiceEvent(ServiceEvent $serviceEvent): void
    {
        $this->serviceEvent = $serviceEvent;
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(_('Events'), 'fa fa-calendar-alt');
    }

    public function titleCreate(): PageTitle
    {
        return new PageTitle(_('Add event'), 'fa fa-calendar-plus');
    }

    /**
     * @throws ModelNotFoundException
     */
    public function titleEdit(): PageTitle
    {
        return new PageTitle(sprintf(_('Edit event %s'), $this->getEntity()->name), 'fa fa-calendar-day');
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
     */
    protected function createComponentEditForm(): EventFormComponent
    {
        return new EventFormComponent($this->getSelectedContestYear(), $this->getContext(), $this->getEntity());
    }

    protected function getORMService(): ServiceEvent
    {
        return $this->serviceEvent;
    }

    /**
     * @param Resource|string|null $resource
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }
}
