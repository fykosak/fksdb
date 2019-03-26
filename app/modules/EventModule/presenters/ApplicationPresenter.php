<?php

namespace EventModule;

use Events\Model\ApplicationHandlerFactory;
use Events\Model\Grid\SingleEventSource;
use FKSDB\Components\Controls\Stalking\Helpers\NotSetControl;
use FKSDB\Components\Events\ApplicationComponent;
use FKSDB\Components\Grids\Events\ParticipantsGrid;
use FKSDB\Logging\FlashDumpFactory;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Models\ModelEventParticipant;
use FKSDB\ORM\Services\ServiceEventParticipant;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

/**
 * Class ApplicationPresenter
 * @package EventModule
 */
class ApplicationPresenter extends BasePresenter {
    /**
     * @var ServiceEventParticipant
     */
    private $serviceEventParticipant;
    /**
     * @var ModelEventParticipant
     */
    private $model;
    /**
     * @var ApplicationHandlerFactory
     */
    private $applicationHandlerFactory;
    /**
     * @var FlashDumpFactory
     */
    private $dumpFactory;

    /**
     * @param ServiceEventParticipant $serviceEventParticipant
     */
    public function injectServiceEventParticipant(ServiceEventParticipant $serviceEventParticipant) {
        $this->serviceEventParticipant = $serviceEventParticipant;
    }

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
     * @return ParticipantsGrid
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function createComponentParticipantsGrid(): ParticipantsGrid {
        return new ParticipantsGrid($this->getEvent());
    }

    /**
     * @return NotSetControl
     */
    public function createComponentNotSet(): NotSetControl {
        return new NotSetControl($this->getTranslator());
    }

    public function titleList() {
        $this->setTitle(_('List of applications'));
        $this->setIcon('fa fa-users');
    }

    public function titleDetail() {
        $this->setTitle(_('Application detail'));
        $this->setIcon('fa fa-user');
    }

    protected function startup() {
        parent::startup();
        if (\in_array($this->getEvent()->event_type_id, [1, 9])) {
            $this->flashMessage(_('Thi GUI don\'t works for team applications.'), self::FLASH_INFO);
        }
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedDetail() {
        $this->setAuthorized($this->eventIsAllowed('event.application', 'detail'));
    }

    /**
     * @throws \Nette\Application\AbortException
     * @throws \Nette\Application\BadRequestException
     */
    public function authorizedList() {
        $this->setAuthorized($this->eventIsAllowed('event.application', 'list'));
    }

    /**
     * @return ApplicationComponent
     * @throws BadRequestException
     * @throws \Nette\Application\AbortException
     */
    public function createComponentApplicationComponent() {
        $holders = [];
        $handlers = [];
        $flashDump = $this->dumpFactory->createApplication();
        $source = new SingleEventSource($this->getEvent(), $this->container);
        foreach ($source as $key => $holder) {
            $holders[$key] = $holder;
            $handlers[$key] = $this->applicationHandlerFactory->create($this->getEvent(), new MemoryLogger()); //TODO it's a bit weird to create new logger for each handler
        }

        $component = new ApplicationComponent($handlers[$this->model->getPrimary()], $holders[$this->model->getPrimary()], $flashDump);
        return $component;
    }

    /**
     * @param $id
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws \Nette\Application\AbortException
     */
    public function actionDetail($id) {
        $row = $this->serviceEventParticipant->findByPrimary($id);
        if (!$row) {
            throw new BadRequestException('Model not found');
        }
        $model = ModelEventParticipant::createFromTableRow($row);
        if ($model->event_id != $this->getEvent()->event_id) {
            throw new ForbiddenRequestException();
        }
        $this->model = $model;
    }

    public function renderDetail() {
        $this->template->model = $this->model;
    }
}
