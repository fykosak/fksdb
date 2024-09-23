<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Schedule;

use FKSDB\Components\Schedule\ScheduleList;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\PageTitle;
use Fykosak\Utils\UI\Title;

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
            EventModel::RESOURCE_ID,
            'default',
            $this->getEvent()
        );
    }

    public function renderDefault(): void
    {
        $this->template->items = [
            new NavItem(new Title(null, _('Create group'), 'fas fa-plus'), ':EventSchedule:Group:create'),
            new NavItem(new Title(null, _('All persons'), 'fas fa-users'), ':EventSchedule:Person:list'),
        ];
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
