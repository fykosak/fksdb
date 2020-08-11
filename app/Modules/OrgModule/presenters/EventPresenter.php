<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Entity\Event\EventFormComponent;
use FKSDB\Components\Grids\Events\EventsGrid;
use FKSDB\Entity\ModelNotFoundException;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Modules\Core\PresenterTraits\EntityPresenterTrait;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceEvent;
use FKSDB\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use FKSDB\Exceptions\NotImplementedException;
use Nette\Security\IResource;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * @author Michal Koutný <michal@fykos.cz>
 * @method ModelEvent getEntity()
 */
class EventPresenter extends BasePresenter {
    use EntityPresenterTrait;

    private ServiceEvent $serviceEvent;

    public function injectServiceEvent(ServiceEvent $serviceEvent): void {
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

    /**
     * @return void
     * @throws ModelNotFoundException
     * @throws BadTypeException
     */
    public function actionEdit(): void {
        $this->traitActionEdit();
    }

    /**
     * @return EventsGrid
     * @throws ForbiddenRequestException
     * @throws BadTypeException
     */
    protected function createComponentGrid(): EventsGrid {
        return new EventsGrid($this->getContext(), $this->getSelectedContest(), $this->getSelectedYear());
    }

    /**
     * @return Control
     * @throws ForbiddenRequestException
     * @throws BadTypeException
     */
    protected function createComponentCreateForm(): Control {
        return new EventFormComponent($this->getSelectedContest(), $this->getContext(), $this->getSelectedYear(), true);
    }

    /**
     * @return Control
     * @throws ForbiddenRequestException
     * @throws BadTypeException
     */
    protected function createComponentEditForm(): Control {
        return new EventFormComponent($this->getSelectedContest(), $this->getContext(), $this->getSelectedYear(), false);
    }

    protected function getORMService(): ServiceEvent {
        return $this->serviceEvent;
    }

    /**
     * @param IResource|string|null $resource
     * @param string $privilege
     * @return bool
     * @throws ForbiddenRequestException
     * @throws BadTypeException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->getContestAuthorizator()->isAllowed($resource, $privilege, $this->getSelectedContest());
    }
}
