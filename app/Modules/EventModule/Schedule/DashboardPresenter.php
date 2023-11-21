<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Schedule;

use FKSDB\Components\Schedule\ScheduleList;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use Fykosak\Utils\UI\PageTitle;

class DashboardPresenter extends BasePresenter
{
    protected PersonScheduleService $personScheduleService;

    public function inject(PersonScheduleService $personScheduleService): void
    {
        $this->personScheduleService = $personScheduleService;
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): bool
    {
        return $this->eventAuthorizator->isAllowed(
            'event',
            'default',
            $this->getEvent()
        );
    }

    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Schedule dashboard'), 'fas fa-dashboard');
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentList(): ScheduleList
    {
        return new ScheduleList($this->getContext(), $this->getEvent());
    }
}
