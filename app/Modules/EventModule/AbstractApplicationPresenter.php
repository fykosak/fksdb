<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Events\ApplicationComponent;
use FKSDB\Components\Controls\Events\MassTransitionsComponent;
use FKSDB\Components\Controls\Events\TransitionButtonsComponent;
use FKSDB\Components\Grids\Application\AbstractApplicationsGrid;
use FKSDB\Components\Grids\Schedule\PersonGrid;
use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Events\Model\ApplicationHandler;
use FKSDB\Models\Events\Model\Grid\SingleEventSource;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\Expressions\NeonSchemaException;
use Fykosak\Utils\Logging\MemoryLogger;
use FKSDB\Models\ORM\Services\ServiceEventParticipant;
use Fykosak\Utils\UI\PageTitle;
use FKSDB\Modules\Core\PresenterTraits\EventEntityPresenterTrait;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\Security\Resource;

abstract class AbstractApplicationPresenter extends BasePresenter
{
    use EventEntityPresenterTrait;

    protected ServiceEventParticipant $serviceEventParticipant;

    final public function injectQuarterly(ServiceEventParticipant $serviceEventParticipant): void
    {
        $this->serviceEventParticipant = $serviceEventParticipant;
    }

    final public function titleList(): PageTitle
    {
        return new PageTitle(_('List of applications'), 'fas fa-address-book');
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws \Throwable
     */
    final public function titleDetail(): PageTitle
    {
        return new PageTitle(sprintf(_('Application detail "%s"'), $this->getEntity()->__toString()), 'fa fa-user');
    }

    final public function titleTransitions(): PageTitle
    {
        return new PageTitle(_('Group transitions'), 'fa fa-exchange-alt');
    }

    /**
     * @throws EventNotFoundException
     */
    public function renderDetail(): void
    {
        $this->template->event = $this->getEvent();
        $this->template->hasSchedule = ($this->getEvent()->getScheduleGroups()->count() !== 0);
    }

    /**
     * @throws EventNotFoundException
     */
    final public function renderList(): void
    {
        $this->template->event = $this->getEvent();
    }

    /**
     * @param Resource|string|null $resource
     * @throws EventNotFoundException
     */
    protected function traitIsAuthorized($resource, ?string $privilege): bool
    {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }

    protected function createComponentPersonScheduleGrid(): PersonGrid
    {
        return new PersonGrid($this->getContext());
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws NeonSchemaException
     * @throws CannotAccessModelException
     */
    protected function createComponentApplicationComponent(): ApplicationComponent
    {
        $source = new SingleEventSource($this->getEvent(), $this->getContext(), $this->eventDispatchFactory);
        return new ApplicationComponent(
            $this->getContext(),
            new ApplicationHandler($this->getEvent(), new MemoryLogger(), $this->getContext()),
            $source->getHolder($this->getEntity()->getPrimary())
        );
    }

    /**
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws NeonSchemaException
     * @throws CannotAccessModelException
     */
    protected function createComponentApplicationTransitions(): TransitionButtonsComponent
    {
        $source = new SingleEventSource($this->getEvent(), $this->getContext(), $this->eventDispatchFactory);
        return new TransitionButtonsComponent(
            $this->getContext(),
            new ApplicationHandler($this->getEvent(), new MemoryLogger(), $this->getContext()),
            $source->getHolder($this->getEntity()->getPrimary())
        );
    }

    /**
     * @throws EventNotFoundException
     */
    final protected function createComponentMassTransitions(): MassTransitionsComponent
    {
        return new MassTransitionsComponent($this->getContext(), $this->getEvent());
    }

    abstract protected function createComponentGrid(): AbstractApplicationsGrid;

    /**
     * @throws NotImplementedException
     */
    protected function createComponentCreateForm(): Control
    {
        throw new NotImplementedException();
    }

    /**
     * @throws NotImplementedException
     */
    protected function createComponentEditForm(): Control
    {
        throw new NotImplementedException();
    }
}
