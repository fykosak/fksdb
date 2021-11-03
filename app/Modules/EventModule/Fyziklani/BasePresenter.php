<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Fyziklani;

use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\ORM\Models\ModelEventType;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\Modules\EventModule\BasePresenter as EventBasePresenter;

abstract class BasePresenter extends EventBasePresenter
{

    protected ServiceFyziklaniTeam $serviceFyziklaniTeam;
    protected ServiceFyziklaniSubmit $serviceFyziklaniSubmit;

    final public function injectFyziklaniBase(
        ServiceFyziklaniSubmit $serviceFyziklaniSubmit,
        ServiceFyziklaniTeam $serviceFyziklaniTeam
    ): void {
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    /**
     * @throws EventNotFoundException
     */
    protected function isEnabled(): bool
    {
        return $this->getEvent()->event_type_id === ModelEventType::FYZIKLANI;
    }

    /**
     * @return string[]
     */
    protected function getNavRoots(): array
    {
        return ['Fyziklani.Dashboard.default'];
    }
}
