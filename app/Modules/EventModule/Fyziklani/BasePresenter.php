<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Fyziklani;

use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\ORM\Models\ModelEventType;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService;
use FKSDB\Modules\EventModule\BasePresenter as EventBasePresenter;

abstract class BasePresenter extends EventBasePresenter
{

    protected TeamService $teamService;
    protected SubmitService $submitService;

    final public function injectFyziklaniBase(
        SubmitService $submitService,
        TeamService $teamService
    ): void {
        $this->submitService = $submitService;
        $this->teamService = $teamService;
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
        return ['Fyziklani.Dashboard.default', 'Fyziklani.Statistics.table'];
    }
}
