<?php

namespace FyziklaniModule;

use EventModule\BasePresenter as EventBasePresenter;
use FKSDB\Components\Controls\Choosers\FyziklaniChooser;
use FKSDB\ORM\Models\ModelEventType;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Application\BadRequestException;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
abstract class BasePresenter extends EventBasePresenter {

    private ServiceFyziklaniTeam $serviceFyziklaniTeam;

    private ServiceFyziklaniSubmit $serviceFyziklaniSubmit;

    public function injectServiceFyziklaniSubmit(ServiceFyziklaniSubmit $serviceFyziklaniSubmit): void {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
    }

    protected function getServiceFyziklaniSubmit(): ServiceFyziklaniSubmit {
        return $this->serviceFyziklaniSubmit;
    }

    public function injectServiceFyziklaniTeam(ServiceFyziklaniTeam $serviceFyziklaniTeam): void {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    protected function getServiceFyziklaniTeam(): ServiceFyziklaniTeam {
        return $this->serviceFyziklaniTeam;
    }

    /**
     * @return FyziklaniChooser
     * @throws BadRequestException
     */
    protected function createComponentFyziklaniChooser(): FyziklaniChooser {
        return new FyziklaniChooser($this->getContext(), $this->getEvent());
    }

    /**
     * @return bool
     * @throws BadRequestException
     */
    protected function isEnabled(): bool {
        return $this->getEvent()->event_type_id === ModelEventType::FYZIKLANI;
    }

    /**
     * @return string[]
     */
    protected function getNavRoots(): array {
        return ['Fyziklani.Dashboard.default'];
    }
}
