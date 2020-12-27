<?php

namespace FKSDB\Modules\FyziklaniModule;

use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Modules\EventModule\BasePresenter as EventBasePresenter;
use FKSDB\Components\Controls\Choosers\FyziklaniChooser;
use FKSDB\Models\ORM\Models\ModelEventType;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeam;

/**
 *
 * @author Michal Červeňák
 * @author Lukáš Timko
 */
abstract class BasePresenter extends EventBasePresenter {

    protected ServiceFyziklaniTeam $serviceFyziklaniTeam;
    protected ServiceFyziklaniSubmit $serviceFyziklaniSubmit;

    final public function injectFyziklaniBase(ServiceFyziklaniSubmit $serviceFyziklaniSubmit, ServiceFyziklaniTeam $serviceFyziklaniTeam): void {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    /**
     * @return FyziklaniChooser
     * @throws EventNotFoundException
     */
    protected function createComponentFyziklaniChooser(): FyziklaniChooser {
        return new FyziklaniChooser($this->getContext(), $this->getEvent());
    }

    /**
     * @return bool
     * @throws EventNotFoundException
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
