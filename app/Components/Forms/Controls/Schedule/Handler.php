<?php

namespace FKSDB\Components\Forms\Controls\Schedule;

use FKSDB\Exceptions\NotImplementedException;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\ORM\Services\Schedule\ServicePersonSchedule;
use FKSDB\ORM\Services\Schedule\ServiceScheduleGroup;
use FKSDB\ORM\Services\Schedule\ServiceScheduleItem;
use Nette\Utils\ArrayHash;
use PDOException;

/**
 * Class Handler
 * @package FKSDB\Components\Forms\Controls\Schedule
 */
class Handler {
    /**
     * @var ServiceScheduleGroup
     */
    private $serviceScheduleGroup;
    /**
     * @var ServicePersonSchedule
     */
    private $servicePersonSchedule;
    /**
     * @var ServiceScheduleItem
     */
    private $serviceScheduleItem;

    /**
     * Handler constructor.
     * @param ServiceScheduleGroup $serviceScheduleGroup
     * @param ServicePersonSchedule $servicePersonSchedule
     * @param ServiceScheduleItem $serviceScheduleItem
     */
    public function __construct(
        ServiceScheduleGroup $serviceScheduleGroup,
        ServicePersonSchedule $servicePersonSchedule,
        ServiceScheduleItem $serviceScheduleItem
    ) {
        $this->serviceScheduleGroup = $serviceScheduleGroup;
        $this->servicePersonSchedule = $servicePersonSchedule;
        $this->serviceScheduleItem = $serviceScheduleItem;
    }

    /**
     * @param ArrayHash $data
     * @param ModelPerson $person
     * @param int $eventId
     * @throws ExistingPaymentException
     * @throws FullCapacityException
     * @throws NotImplementedException
     */
    public function prepareAndUpdate(ArrayHash $data, ModelPerson $person, int $eventId) {
        foreach ($this->prepareData($data) as $type => $newScheduleData) {
            $this->updateDataType($newScheduleData, $type, $person, $eventId);
        }
    }

    /**
     * @param array $newScheduleData
     * @param string $type
     * @param ModelPerson $person
     * @param int $eventId
     * @throws ExistingPaymentException
     * @throws FullCapacityException
     * @throws NotImplementedException
     */
    private function updateDataType(array $newScheduleData, string $type, ModelPerson $person, int $eventId) {
        $oldRows = $this->servicePersonSchedule->getTable()
            ->where('person_id', $person->person_id)
            ->where('schedule_item.schedule_group.event_id', $eventId)->where('schedule_item.schedule_group.schedule_group_type', $type);

        /** @var ModelPersonSchedule $modelPersonSchedule */
        foreach ($oldRows as $modelPersonSchedule) {
            if (\in_array($modelPersonSchedule->schedule_item_id, $newScheduleData)) {
                // do nothing
                $index = \array_search($modelPersonSchedule->schedule_item_id, $newScheduleData);
                unset($newScheduleData[$index]);
            } else {
                try {
                    $modelPersonSchedule->delete();
                } catch (PDOException $exception) {
                    if (\preg_match('/payment/', $exception->getMessage())) {
                        throw new ExistingPaymentException(\sprintf(
                            _('Položka "%s" má už vygenerovanú platu, teda nejde zmazať.'),
                            $modelPersonSchedule->getLabel()));
                    } else {
                        throw $exception;
                    }
                }
            }
        }

        foreach ($newScheduleData as $id) {
            /** @var ModelScheduleItem $modelScheduleItem */
            $modelScheduleItem = $this->serviceScheduleItem->findByPrimary($id);
            if ($modelScheduleItem->hasFreeCapacity()) {
                $this->servicePersonSchedule->createNewModel(['person_id' => $person->person_id, 'schedule_item_id' => $id]);
            } else {
                throw new FullCapacityException(\sprintf(
                    _('Osobu %s nepodarilo ptihlásiť na "%s", z dôvodu plnej kapacity.'),
                    $person->getFullName(),
                    $modelScheduleItem->getLabel()
                ));
            }
        }
    }

    /**
     * @param ArrayHash $data
     * @return int
     */
    private function prepareData(ArrayHash $data): array {
        $newData = [];
        foreach ($data as $type => $datum) {
            $newData[$type] = \array_values((array)\json_decode($datum));
        }
        return $newData;
    }
}
