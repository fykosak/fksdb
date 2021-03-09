<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Events\TransitionButtonsComponent;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Events\Model\ApplicationHandlerFactory;
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
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

/**
 * Class AbstractApplicationPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractApplicationPresenter extends BasePresenter {

    use EventEntityPresenterTrait;

    protected ApplicationHandlerFactory $applicationHandlerFactory;
    protected ServiceEventParticipant $serviceEventParticipant;

    final public function injectQuarterly(ApplicationHandlerFactory $applicationHandlerFactory, ServiceEventParticipant $serviceEventParticipant): void {
        $this->applicationHandlerFactory = $applicationHandlerFactory;
        $this->serviceEventParticipant = $serviceEventParticipant;
    }

    /**
     * @throws ForbiddenRequestException
     */
    final public function titleList(): void {
        $this->setPageTitle(new PageTitle(_('List of applications'), 'fas fa-users'));
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
        $this->setPageTitle(new PageTitle(_('Group transitions'), 'fa fa-user'));
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
    public function renderList(): void {
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
        return new ApplicationComponent($this->getContext(), $this->applicationHandlerFactory->create($this->getEvent(), new MemoryLogger()), $source->getHolder($this->getEntity()->getPrimary()));
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
        return new TransitionButtonsComponent($this->getContext(), $this->applicationHandlerFactory->create($this->getEvent(), new MemoryLogger()), $source->getHolder($this->getEntity()->getPrimary()));
    }

    /**
     * @return MassTransitionsComponent
     * @throws EventNotFoundException
     */
    final protected function createComponentMassTransitions(): MassTransitionsComponent {
        return new MassTransitionsComponent($this->getContext(), $this->getEvent());
    }

    /**
     * @return AbstractApplicationsGrid
     * @throws AbortException
     *
     */
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
