<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Entity\EventFormComponent;
use FKSDB\Components\Grids\Events\EventsGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\ServiceEvent;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use FKSDB\Models\Exceptions\NotImplementedException;
use Nette\Security\IResource;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * @author Michal Koutný <michal@fykos.cz>
 * @method ModelEvent getEntity()
 */
class EventPresenter extends BasePresenter {
    use EntityPresenterTrait;

    private ServiceEvent $serviceEvent;

    final public function injectServiceEvent(ServiceEvent $serviceEvent): void {
        $this->serviceEvent = $serviceEvent;
    }

    public function getTitleList(): PageTitle {
        return new PageTitle(_('Events'), 'fa fa-calendar-check-o');
    }

    public function getTitleCreate(): PageTitle {
        return new PageTitle(_('Add event'), 'fa fa-calendar-plus-o');
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    public function titleEdit(): void {
        $this->setPageTitle(new PageTitle(sprintf(_('Edit event %s'), $this->getEntity()->name), 'fa fa-pencil'));
    }

    /**
     * @throws NotImplementedException
     */
    public function actionDelete(): void {
        throw new NotImplementedException();
    }

    protected function createComponentGrid(): EventsGrid {
        return new EventsGrid($this->getContext(), $this->getSelectedContest(), $this->getSelectedYear());
    }

    protected function createComponentCreateForm(): EventFormComponent {
        return new EventFormComponent($this->getSelectedContest(), $this->getContext(), $this->getSelectedYear(), null);
    }

    /**
     * @return EventFormComponent
     * @throws ModelNotFoundException
     */
    protected function createComponentEditForm(): EventFormComponent {
        return new EventFormComponent($this->getSelectedContest(), $this->getContext(), $this->getSelectedYear(), $this->getEntity());
    }

    protected function getORMService(): ServiceEvent {
        return $this->serviceEvent;
    }

    /**
     * @param IResource|string|null $resource
     * @param string|null $privilege
     * @return bool
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }
}
