<?php

namespace EventModule;

use Events\Model\ApplicationHandlerFactory;
use Events\Model\Grid\SingleEventSource;
use FKSDB\Components\Events\ApplicationComponent;
use FKSDB\Components\Grids\Events\Application\AbstractApplicationGrid;
use FKSDB\Components\Grids\Schedule\PersonGrid;
use FKSDB\Logging\FlashDumpFactory;
use FKSDB\Logging\MemoryLogger;
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
     * @param ApplicationHandlerFactory $applicationHandlerFactory
     */
    public function injectHandlerFactory(ApplicationHandlerFactory $applicationHandlerFactory) {
        $this->applicationHandlerFactory = $applicationHandlerFactory;
    }

    /**
     * @param FlashDumpFactory $dumpFactory
     */
    public function injectFlashDumpFactory(FlashDumpFactory $dumpFactory) {
        $this->dumpFactory = $dumpFactory;
    }

    /**
     * @param int $id
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    public function actionDetail(int $id) {
        $this->loadEntity($id);
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    protected function renderDetail() {
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
            $handlers[$key] = $this->applicationHandlerFactory->create($this->getEvent(), new MemoryLogger()); //TODO it's a bit weird to create new logger for each handler
        }

        return new ApplicationComponent($handlers[$this->getEntity()->getPrimary()], $holders[$this->getEntity()->getPrimary()], $flashDump);
    }

    /**
     * @return bool
     * @throws BadRequestException
     * @throws AbortException
     */
    protected function isTeamEvent(): bool {
        if (in_array($this->getEvent()->event_type_id, self::TEAM_EVENTS)) {
            $this->setAuthorized(false);
            return true;
        }
        return false;
    }

    /**
     * @return void
     */
    abstract public function titleList();

    /**
     * @return void
     */
    abstract public function titleDetail();

    /**
     * @return AbstractApplicationGrid
     * @throws AbortException
     * @throws BadRequestException
     */
    abstract function createComponentGrid(): AbstractApplicationGrid;
}
