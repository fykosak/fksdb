<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule\Schedule;

use FKSDB\Components\Schedule\Timeline\ScheduleTimeline;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
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
        return new PageTitle(null, _('Errors'), 'fas fa-list');
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedError(): bool
    {
        return $this->contestAuthorizator->isAllowed(
            PersonScheduleModel::RESOURCE_ID,
            'error',
            $this->getEvent()->event_type->contest
        );
    }

    public function titleError(): PageTitle
    {
        return new PageTitle(null, _('Errors'), 'fas fa-list');
    }

    public function renderError(): void
    {
        $query = $this->service->getTable()->where(
            'schedule_item.schedule_group.event_id',
            $this->getEvent()->event_id
        );
        $roleCache = [];
        $errors = [];
        /** @var PersonScheduleModel $personSchedule */
        foreach ($query as $personSchedule) {
            if (!isset($roleCache[$personSchedule->person_id])) {
                $roleCache[$personSchedule->person_id] = $personSchedule->person->getEventRoles($this->getEvent());
            }
            $roles = $roleCache[$personSchedule->person_id];
            if (!count($roles)) {
                $errors[] = $personSchedule;
            }
        }
    }

    protected function createComponentScheduleTimeline(): ScheduleTimeline
    {
        return new ScheduleTimeline($this->getContext(), $this->getEvent());
    }
}
