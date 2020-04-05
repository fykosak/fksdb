<?php

namespace EventModule;

use Events\Model\ApplicationHandlerFactory;
use Events\Model\Grid\SingleEventSource;
use FKSDB\Components\Events\ApplicationComponent;
use FKSDB\Components\Events\MassTransitionsControl;
use FKSDB\Components\Grids\Events\Application\AbstractApplicationGrid;
use FKSDB\Components\Grids\Schedule\PersonGrid;
use FKSDB\Logging\MemoryLogger;
use FKSDB\NotImplementedException;
use FKSDB\ORM\Services\ServiceEventParticipant;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
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
    public final function titleList() {
        $this->setTitle(_('List of applications'), 'fa fa-users');
    }

    public final function titleDetail() {
        $this->setTitle(_('Application detail'), 'fa fa-user');
    }

    public final function titleTransitions() {
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
     */
    protected function renderDetail(int $id) {
        $this->template->event = $this->getEvent();
        $this->template->model = $this->loadEntity($id);
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
    protected final function createComponentPersonScheduleGrid(): PersonGrid {
        return new PersonGrid($this->getContext());
    }

    /**
     * @return ApplicationComponent
     * @throws BadRequestException
     * @throws AbortException
     */
    public function createComponentApplicationComponent(): ApplicationComponent {
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
    public final function createComponentMassTransitions(): MassTransitionsControl {
        return new MassTransitionsControl($this->getContext(), $this->getEvent());
    }

    /**
     * @return AbstractApplicationGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    abstract function createComponentGrid(): AbstractApplicationGrid;

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
