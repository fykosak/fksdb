<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Schedule;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use Nette\InvalidStateException;

class Handler
{
    private PersonScheduleService $personScheduleService;

    public function __construct(PersonScheduleService $personScheduleService)
    {
        $this->personScheduleService = $personScheduleService;
    }

    /**
     * @throws ExistingPaymentException
     * @throws FullCapacityException
     */
    public function prepareAndUpdate(array $data, PersonModel $person, EventModel $event, string $lang): void
    {
        foreach ($data as $type => $items) {
            foreach ($items as $groupId => $item) {
                /** @var ScheduleGroupModel|null $group */
                $group = $event->getScheduleGroups()
                    ->where('schedule_group_type', $type)
                    ->where('schedule_group_id', $groupId)
                    ->fetch();
                if (!$group) {
                    throw new InvalidStateException(_('Schedule group does not exists'));
                }
                $this->saveGroup($person, $group, $item, $lang);
            }
        }
    }

    public function saveGroup(PersonModel $person, ScheduleGroupModel $group, ?int $value, string $lang): void
    {
        $personSchedule = $person->getScheduleByGroup($group);
        if ($value) {
            /** @var ScheduleItemModel|null $item */
            $item = $group->getItems()->where('schedule_item_id', $value)->fetch();
            if (!$item) {
                throw new InvalidStateException(sprintf(_('Item with Id %s does not exists'), $value));
            }
            // create
            if (!$personSchedule) {
                if (!$group->canCreate()) {
                    throw new InvalidStateException(_('Registration is not open at this time'));
                }
            } else {
                // already booked
                if ($personSchedule->schedule_item_id === $item->schedule_item_id) {
                    return;
                }
                if (!$group->canEdit()) {
                    throw new InvalidStateException(_('Registration is not open at this time'));
                }
            }
            if (!$item->hasFreeCapacity()) {
                throw new FullCapacityException(
                    sprintf(
                        _('The person %s could not be registered for "%s" because of full capacity.'),
                        $person->getFullName(),
                        $lang === 'cs' ? $item->name_cs : $item->name_en
                    )
                );
            }
            $this->personScheduleService->storeModel(
                ['person_id' => $person->person_id, 'schedule_item_id' => $value],
                $personSchedule
            );
        } elseif ($personSchedule) {
            if (!$group->canEdit()) {
                throw new InvalidStateException(_('Registration is not open at this time'));
            }
            $this->personScheduleService->disposeModel($personSchedule);
        }
    }
}
