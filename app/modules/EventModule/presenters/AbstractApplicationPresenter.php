<?php

namespace EventModule;

use FKSDB\Config\NeonSchemaException;
use FKSDB\Events\Model\ApplicationHandlerFactory;
use FKSDB\Events\Model\Grid\SingleEventSource;
use FKSDB\Components\Events\ApplicationComponent;
use FKSDB\Components\Events\MassTransitionsControl;
use FKSDB\Components\Grids\Events\Application\AbstractApplicationGrid;
use FKSDB\Components\Grids\Schedule\PersonGrid;
use FKSDB\Logging\MemoryLogger;
use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Services\ServiceEventParticipant;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\InvalidStateException;

/**
 * Class AbstractApplicationPresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractApplicationPresenter extends BasePresenter {
    use EventEntityTrait;

    protected ApplicationHandlerFactory $applicationHandlerFactory;

    protected ServiceEventParticipant $serviceEventParticipant;

    public function injectHandlerFactory(ApplicationHandlerFactory $applicationHandlerFactory): void {
        $this->applicationHandlerFactory = $applicationHandlerFactory;
    }

    public function injectServiceEventParticipant(ServiceEventParticipant $serviceEventParticipant): void {
        $this->serviceEventParticipant = $serviceEventParticipant;
    }

    /**
     * @throws BadRequestException
     */
    final public function titleList(): void {
        $this->setTitle(_('List of applications'), 'fa fa-users');
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws \Throwable
     */
    final public function titleDetail(int $id): void {
        $this->setTitle(sprintf(_('Application detail "%s"'), $this->loadEntity($id)->__toString()), 'fa fa-user');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    final public function titleTransitions(): void {
        $this->setTitle(_('Group transitions'), 'fa fa-user');
    }

    /**
     * @param $resource
     * @param string $privilege
     * @return bool
     * @throws BadRequestException
     */
    protected function traitIsAuthorized($resource, string $privilege): bool {
        return $this->isContestsOrgAuthorized($resource, $privilege);
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    protected function actionDetail(int $id): void {
        $this->loadEntity($id);
    }

    /**
     * @param int $id
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     */
    public function renderDetail(int $id): void {
        $this->template->event = $this->getEvent();
        $this->template->hasSchedule = ($this->getEvent()->getScheduleGroups()->count() !== 0);
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function renderList(): void {
        $this->template->event = $this->getEvent();
    }

    protected function createComponentPersonScheduleGrid(): PersonGrid {
        return new PersonGrid($this->getContext());
    }

    /**
     * @return ApplicationComponent
     * @throws BadRequestException
     * @throws AbortException
     * @throws NeonSchemaException
     */
    protected function createComponentApplicationComponent(): ApplicationComponent {
        $source = new SingleEventSource($this->getEvent(), $this->getContext());
        foreach ($source->getHolders() as $key => $holder) {
            if ($key === $this->getEntity()->getPrimary()) {
                return new ApplicationComponent($this->getContext(), $this->applicationHandlerFactory->create($this->getEvent(), new MemoryLogger()), $holder);
            }
        }
        throw new InvalidStateException();
    }

    /**
     * @return MassTransitionsControl
     * @throws AbortException
     * @throws BadRequestException
     */
    final protected function createComponentMassTransitions(): MassTransitionsControl {
        return new MassTransitionsControl($this->getContext(), $this->getEvent());
    }

    /**
     * @return AbstractApplicationGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    abstract protected function createComponentGrid(): AbstractApplicationGrid;

    /**
     * @return Control
     * @throws NotImplementedException
     */
    public function createComponentCreateForm(): Control {
        throw new NotImplementedException();
    }

    /**
     * @return Control
     * @throws NotImplementedException
     */
    public function createComponentEditForm(): Control {
        throw new NotImplementedException();
    }
}
