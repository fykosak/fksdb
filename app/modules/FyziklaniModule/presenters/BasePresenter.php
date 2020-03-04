<?php

namespace FyziklaniModule;

use EventModule\BasePresenter as EventBasePresenter;
use FKSDB\Components\Controls\Choosers\FyziklaniChooser;
use FKSDB\Components\Factories\FyziklaniFactory;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
abstract class BasePresenter extends EventBasePresenter {

    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;

    /**
     * @var ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;

    /**
     * @var ServiceFyziklaniSubmit
     */
    private $serviceFyziklaniSubmit;

    /**
     * @var FyziklaniFactory
     */
    protected $fyziklaniComponentsFactory;

    /**
     * @param FyziklaniFactory $fyziklaniComponentsFactory
     */
    public function injectFyziklaniComponentsFactory(FyziklaniFactory $fyziklaniComponentsFactory): void {
        $this->fyziklaniComponentsFactory = $fyziklaniComponentsFactory;
    }

    /**
     * @param ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     */
    public function injectServiceFyziklaniSubmit(ServiceFyziklaniSubmit $serviceFyziklaniSubmit): void {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
    }

    /**
     * @return ServiceFyziklaniSubmit
     */
    protected function getServiceFyziklaniSubmit(): ServiceFyziklaniSubmit {
        return $this->serviceFyziklaniSubmit;
    }

    /**
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     */
    public function injectServiceFyziklaniTeam(ServiceFyziklaniTeam $serviceFyziklaniTeam): void {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    /**
     * @return ServiceFyziklaniTeam
     */
    protected function getServiceFyziklaniTeam(): ServiceFyziklaniTeam {
        return $this->serviceFyziklaniTeam;
    }

    /**
     * @param ServiceFyziklaniTask $serviceFyziklaniTask
     */
    public function injectServiceFyziklaniTask(ServiceFyziklaniTask $serviceFyziklaniTask): void {
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
    }

    /**
     * @return ServiceFyziklaniTask
     */
    protected function getServiceFyziklaniTask(): ServiceFyziklaniTask {
        return $this->serviceFyziklaniTask;
    }

    /**
     * @return FyziklaniChooser
     */
    protected function createComponentFyziklaniChooser(): FyziklaniChooser {
        return new FyziklaniChooser($this->serviceEvent);
    }

    /**
     * @param ModelEvent $event
     * @return bool
     */
    protected function isEnabledForEvent(ModelEvent $event): bool {
        return $event->event_type_id === 1;
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    protected function startup(): void {
        parent::startup();
        /**
         * @var FyziklaniChooser $fyziklaniChooser
         */
        $fyziklaniChooser = $this->getComponent('fyziklaniChooser');
        $fyziklaniChooser->setEvent($this->getEvent());
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection
     * @return string[]
     */
    protected function getNavRoots(): array {
        return ['fyziklani.dashboard.default'];
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection
     * @return int
     */
    protected function getEventId(): int {
        if (!$this->eventId) {
            $this->eventId = $this->serviceEvent->getTable()->where('event_type_id', 1)->max('event_id');
        }
        return $this->eventId;
    }
}
