<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Fyziklani;

use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\ORM\Models\EventTypeModel;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Modules\EventModule\BasePresenter as EventBasePresenter;

abstract class BasePresenter extends EventBasePresenter
{

    protected TeamService2 $teamService;
    protected SubmitService $submitService;

    final public function injectFyziklaniBase(
        SubmitService $submitService,
        TeamService2 $teamService
    ): void {
        $this->submitService = $submitService;
        $this->teamService = $teamService;
    }

    /**
     * @throws EventNotFoundException
     */
    protected function isEnabled(): bool
    {
        return $this->getEvent()->event_type_id === EventTypeModel::FYZIKLANI;
    }

    /**
     * @return string[]
     */
    protected function getNavRoots(): array
    {
        return ['Fyziklani.Dashboard.default', 'Fyziklani.Statistics.table'];
    }
}
