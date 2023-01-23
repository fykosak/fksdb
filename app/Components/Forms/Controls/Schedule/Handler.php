<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Schedule;

use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Models\ORM\Services\Schedule\ScheduleItemService;
use Fykosak\NetteORM\Exceptions\ModelException;
use Nette\InvalidStateException;

class Handler
{
    private PersonScheduleService $personScheduleService;
    private ScheduleItemService $scheduleItemService;

    public function __construct(
        PersonScheduleService $personScheduleService,
        ScheduleItemService $scheduleItemService
    ) {
        $this->personScheduleService = $personScheduleService;
        $this->scheduleItemService = $scheduleItemService;
    }

    /**
     * @throws ExistingPaymentException
     * @throws FullCapacityException
     * @throws NotImplementedException
     */
    public function prepareAndUpdate(array $data, PersonModel $person, EventModel $event): void
    {
        foreach ($data as $groupId => $items) {
            $group = $event->getScheduleGroups()->where('schedule_group_id', $groupId)->fetch();
            if (!$group) {
                throw new InvalidStateException(_('Schedule group does not exists'));
            }
            $this->saveGroup($person,$group,$items);
        }
    }

    /**
     * @throws ExistingPaymentException
     * @throws FullCapacityException
     * @throws NotImplementedException
     * @throws \PDOException
     * @throws ModelException
     */
    private function updateDataType(array $newScheduleData, string $type, PersonModel $person, EventModel $event): void
    {
        $oldRows = $person->getScheduleForEvent($event)->where(
            'schedule_item.schedule_group.schedule_group_type',
            $type
        );

        /** @var PersonScheduleModel $modelPersonSchedule */
        foreach ($oldRows as $oldRow) {
            $modelPersonSchedule = $oldRow;
            if (in_array($modelPersonSchedule->schedule_item_id, $newScheduleData)) {
                // do nothing
                $index = array_search($modelPersonSchedule->schedule_item_id, $newScheduleData);
                unset($newScheduleData[$index]);
            } else {
                try {
                    $this->personScheduleService->disposeModel($modelPersonSchedule);
                } catch (\PDOException $exception) {
                    if (preg_match('/payment/', $exception->getMessage())) {
                        throw new ExistingPaymentException(
                            sprintf(
                                _('The item "%s" has already a payment generated, so it cannot be deleted.'),
                                $modelPersonSchedule->getLabel()
                            )
                        );
                    } else {
                        throw $exception;
                    }
                }
            }
        }

        foreach ($newScheduleData as $id) {
            /** @var ScheduleItemModel $modelScheduleItem */
            $modelScheduleItem = $this->scheduleItemService->findByPrimary($id);
            if ($modelScheduleItem->hasFreeCapacity()) {
                $this->personScheduleService->storeModel(
                    ['person_id' => $person->person_id, 'schedule_item_id' => $id]
                );
            } else {
                throw new FullCapacityException(
                    sprintf(
                        _('The person %s could not be registered for "%s" because of full capacity.'),
                        $person->getFullName(),
                        $modelScheduleItem->getLabel()
                    )
                );
            }
        }
    }

    private function prepareData(array $data): array
    {
        $newData = [];
        foreach ($data as $type => $datum) {
            $newData[$type] = $datum ? array_values((array)json_decode($datum)) : [];
        }
        return $newData;
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
