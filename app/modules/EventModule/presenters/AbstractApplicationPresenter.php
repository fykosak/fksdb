<?php

namespace EventModule;

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
 * Class ApplicationPresenter
 * @package EventModule
 */
abstract class AbstractApplicationPresenter extends BasePresenter {
    use EventEntityTrait;

    /** @var ApplicationHandlerFactory */
    protected $applicationHandlerFactory;

    /** @var ServiceEventParticipant */
    protected $serviceEventParticipant;

    /**
     * @param ApplicationHandlerFactory $applicationHandlerFactory
     */
    public function injectHandlerFactory(ApplicationHandlerFactory $applicationHandlerFactory) {
        $this->applicationHandlerFactory = $applicationHandlerFactory;
    }

    /**
     * @param ServiceEventParticipant $serviceEventParticipant
     */
    public function injectServiceEventParticipant(ServiceEventParticipant $serviceEventParticipant) {
        $this->serviceEventParticipant = $serviceEventParticipant;
    }

    final public function titleList() {
        $this->setTitle(_('List of applications'), 'fa fa-users');
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    final public function titleDetail(int $id) {
        $this->setTitle(sprintf(_('Application detail "%s"'), $this->loadEntity($id)->__toString()), 'fa fa-user');
    }

    final public function titleTransitions() {
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
    protected function actionDetail(int $id) {
        $this->loadEntity($id);
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     */
    public function renderDetail(int $id) {
        $this->template->event = $this->getEvent();
        $this->template->hasSchedule = ($this->getEvent()->getScheduleGroups()->count() !== 0);
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    public function renderList() {
        $this->template->event = $this->getEvent();
    }

    /**
     * @return PersonGrid
     */
    protected function createComponentPersonScheduleGrid(): PersonGrid {
        return new PersonGrid($this->getContext());
    }

    /**
     * @return ApplicationComponent
     * @throws BadRequestException
     * @throws AbortException
     */
    protected function createComponentApplicationComponent(): ApplicationComponent {
        $source = new SingleEventSource($this->getEvent(), $this->getContext());
        foreach ($source->getHolders() as $key => $holder) {
            if ($key === $this->getEntity()->getPrimary()) {
                return new ApplicationComponent($this->applicationHandlerFactory->create($this->getEvent(), new MemoryLogger()), $holder);
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
     * @inheritDoc
     */
    public function createComponentCreateForm(): Control {
        throw new NotImplementedException();
    }

    /**
     * @inheritDoc
     */
    public function createComponentEditForm(): Control {
        throw new NotImplementedException();
    }
}
