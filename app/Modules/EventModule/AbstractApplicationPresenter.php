<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Events\TransitionButtonsComponent;
use FKSDB\Models\Events\Model\ApplicationHandler;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Events\Model\Grid\SingleEventSource;
use FKSDB\Components\Controls\Events\ApplicationComponent;
use FKSDB\Components\Controls\Events\MassTransitionsComponent;
use FKSDB\Components\Grids\Application\AbstractApplicationsGrid;
use FKSDB\Components\Grids\Schedule\PersonGrid;
use FKSDB\Models\Logging\MemoryLogger;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use FKSDB\Models\ORM\Services\ServiceEventParticipant;
use FKSDB\Models\UI\PageTitle;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

abstract class AbstractApplicationPresenter extends BasePresenter {

    use EventEntityPresenterTrait;

    protected ServiceEventParticipant $serviceEventParticipant;

    final public function injectQuarterly(ServiceEventParticipant $serviceEventParticipant): void {
        $this->serviceEventParticipant = $serviceEventParticipant;
    }

    /**
     * @throws ForbiddenRequestException
     */
    final public function titleList(): void {
        $this->setPageTitle(new PageTitle(_('List of applications'), 'fas fa-address-book'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws \Throwable
     */
    final public function titleDetail(): void {
        $this->setPageTitle(new PageTitle(sprintf(_('Application detail "%s"'), $this->getEntity()->__toString()), 'fa fa-user'));
    }

    /**
     * @return void
     * @throws ForbiddenRequestException
     */
    final public function titleTransitions(): void {
        $this->setPageTitle(new PageTitle(_('Group transitions'), 'fa fa-exchange-alt'));
    }

    /**
     * @param Resource|string|null $resource
     * @param string|null $privilege
     * @return bool
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function renderDetail(): void {
        $this->template->event = $this->getEvent();
        $this->template->hasSchedule = ($this->getEvent()->getScheduleGroups()->count() !== 0);
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    final public function renderList(): void {
        $this->template->event = $this->getEvent();
    }

    protected function createComponentPersonScheduleGrid(): PersonGrid {
        return new PersonGrid($this->getContext());
    }

    /**
     * @return ApplicationComponent
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws NeonSchemaException
     * @throws CannotAccessModelException
     */
    protected function createComponentApplicationComponent(): ApplicationComponent {
        $source = new SingleEventSource($this->getEvent(), $this->getContext(), $this->eventDispatchFactory);
        return new ApplicationComponent($this->getContext(), new ApplicationHandler($this->getEvent(), new MemoryLogger(), $this->getContext()), $source->getHolder($this->getEntity()->getPrimary()));
    }

    /**
     * @return TransitionButtonsComponent
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws NeonSchemaException
     * @throws CannotAccessModelException
     */
    protected function createComponentApplicationTransitions(): TransitionButtonsComponent {
        $source = new SingleEventSource($this->getEvent(), $this->getContext(), $this->eventDispatchFactory);
        return new TransitionButtonsComponent($this->getContext(), new ApplicationHandler($this->getEvent(), new MemoryLogger(), $this->getContext()), $source->getHolder($this->getEntity()->getPrimary()));
    }

    /**
     * @return MassTransitionsComponent
     * @throws EventNotFoundException
     */
    final protected function createComponentMassTransitions(): MassTransitionsComponent {
        return new MassTransitionsComponent($this->getContext(), $this->getEvent());
    }

    abstract protected function createComponentGrid(): AbstractApplicationsGrid;

    /**
     * @return Control
     * @throws NotImplementedException
     */
    protected function createComponentCreateForm(): Control {
        throw new NotImplementedException();
    }

    /**
     * @return Control
     * @throws NotImplementedException
     */
    protected function createComponentEditForm(): Control {
        throw new NotImplementedException();
    }
}
