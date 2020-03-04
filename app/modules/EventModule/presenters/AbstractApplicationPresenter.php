<?php

namespace EventModule;

use Events\Model\ApplicationHandlerFactory;
use Events\Model\Grid\SingleEventSource;
use FKSDB\Components\Events\ApplicationComponent;
use FKSDB\Components\Grids\Events\Application\AbstractApplicationGrid;
use FKSDB\Components\Grids\Schedule\PersonGrid;
use FKSDB\Components\React\ReactComponent\Events\SingleApplicationsTimeProgress;
use FKSDB\Logging\FlashDumpFactory;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\ServiceEventParticipant;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use function in_array;

/**
 * Class ApplicationPresenter
 * @package EventModule
 */
abstract class AbstractApplicationPresenter extends BasePresenter {
    use EventEntityTrait;

    /**
     * @var ApplicationHandlerFactory
     */
    protected $applicationHandlerFactory;
    /**
     * @var FlashDumpFactory
     */
    protected $dumpFactory;

    /**
     * @var ServiceEventParticipant
     */
    protected $serviceEventParticipant;

    /**
     * @param ApplicationHandlerFactory $applicationHandlerFactory
     */
    public function injectHandlerFactory(ApplicationHandlerFactory $applicationHandlerFactory): void {
        $this->applicationHandlerFactory = $applicationHandlerFactory;
    }

    /**
     * @param FlashDumpFactory $dumpFactory
     */
    public function injectFlashDumpFactory(FlashDumpFactory $dumpFactory): void {
        $this->dumpFactory = $dumpFactory;
    }

    /**
     * @param ServiceEventParticipant $serviceEventParticipant
     */
    public function injectServiceEventParticipant(ServiceEventParticipant $serviceEventParticipant): void {
        $this->serviceEventParticipant = $serviceEventParticipant;
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function actionDetail(int $id): void {
        $this->loadEntity($id);
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function renderDetail(): void {
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

    /**
     * @return PersonGrid
     */
    protected function createComponentPersonScheduleGrid(): PersonGrid {
        return new PersonGrid($this->getTableReflectionFactory());
    }

    /**
     * @return ApplicationComponent
     * @throws BadRequestException
     * @throws AbortException
     */
    public function createComponentApplicationComponent(): ApplicationComponent {
        $holders = [];
        $handlers = [];
        $flashDump = $this->dumpFactory->create('application');
        $source = new SingleEventSource($this->getEvent(), $this->container);
        foreach ($source as $key => $holder) {
            $holders[$key] = $holder;
            $handlers[$key] = $this->applicationHandlerFactory->create($this->getEvent(), new MemoryLogger());
        }

        return new ApplicationComponent($handlers[$this->getEntity()->getPrimary()], $holders[$this->getEntity()->getPrimary()], $flashDump);
    }

    /**
     * @return SingleApplicationsTimeProgress
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function createComponentSingleApplicationsTimeProgress(): SingleApplicationsTimeProgress {
        $events = [];
        foreach ($this->getEventIdsByType() as $id) {
            $row = $this->serviceEvent->findByPrimary($id);
            $events[$id] = ModelEvent::createFromActiveRow($row);
        }
        return new SingleApplicationsTimeProgress($this->context, $events, $this->serviceEventParticipant);
    }

    /**
     * @return int[]
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function getEventIdsByType(): array {
        return array_values($this->serviceEvent->getEventsByType($this->getEvent()->getEventType())->fetchPairs('event_id', 'event_id'));
    }

    /**
     * @return bool
     * @throws BadRequestException
     * @throws AbortException
     */
    protected function isTeamEvent(): bool {
        if (in_array($this->getEvent()->event_type_id, self::TEAM_EVENTS)) {
            return true;
        }
        return false;
    }

    /**
     * @return void
     */
    abstract public function titleList(): void;

    /**
     * @return void
     */
    abstract public function titleDetail(): void;

    /**
     * @return AbstractApplicationGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    abstract function createComponentGrid(): AbstractApplicationGrid;
}
