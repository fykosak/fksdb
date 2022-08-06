<?php

declare(strict_types=1);

namespace FKSDB\Components\Forms\Controls\Schedule;

use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\PersonScheduleModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleItemModel;
use FKSDB\Models\ORM\Services\Schedule\PersonScheduleService;
use FKSDB\Models\ORM\Services\Schedule\ScheduleItemService;

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
        foreach ($this->prepareData($data) as $type => $newScheduleData) {
            $this->updateDataType($newScheduleData, $type, $person, $event);
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
            $modelPersonSchedule = PersonScheduleModel::createFromActiveRow($oldRow);
            if (in_array($modelPersonSchedule->schedule_item_id, $newScheduleData)) {
                // do nothing
                $index = array_search($modelPersonSchedule->schedule_item_id, $newScheduleData);
                unset($newScheduleData[$index]);
            } else {
                try {
                    $modelPersonSchedule->delete();
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
                $this->personScheduleService->createNewModel(
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
            $newData[$type] = array_values((array)json_decode($datum));
        }
        return $newData;
    }
}
