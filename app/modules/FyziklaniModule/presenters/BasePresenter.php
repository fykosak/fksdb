<?php

namespace FyziklaniModule;

use EventModule\BasePresenter as EventBasePresenter;
use FKSDB\Components\Controls\Choosers\FyziklaniChooser;
use FKSDB\Components\Factories\FyziklaniFactory;
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
    public function injectFyziklaniComponentsFactory(FyziklaniFactory $fyziklaniComponentsFactory) {
        $this->fyziklaniComponentsFactory = $fyziklaniComponentsFactory;
    }

    /**
     * @param ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     */
    public function injectServiceFyziklaniSubmit(ServiceFyziklaniSubmit $serviceFyziklaniSubmit) {
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
    public function injectServiceFyziklaniTeam(ServiceFyziklaniTeam $serviceFyziklaniTeam) {
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
    public function injectServiceFyziklaniTask(ServiceFyziklaniTask $serviceFyziklaniTask) {
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
     * @return bool
     * @throws BadRequestException
     */
    protected function isEnabledForEvent(): bool {
        return $this->getEvent()->event_type_id === 1;
    }

    /**
     * @throws BadRequestException
     * @throws AbortException
     */
    protected function startup() {
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
}
