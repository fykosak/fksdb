<?php

namespace FKSDB\Components\Forms\Controls\Schedule;

use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\ORM\Services\Schedule\ServicePersonSchedule;
use FKSDB\ORM\Services\Schedule\ServiceScheduleGroup;
use FKSDB\ORM\Services\Schedule\ServiceScheduleItem;
use Nette\Utils\ArrayHash;

/**
 * Class Handler
 * @package FKSDB\Components\Forms\Controls\PersonAccommodation
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
     * @param $eventId
     *
     * @throws ExistingPaymentException
     * @throws FullCapacityException
     */
    public function prepareAndUpdate(ArrayHash $data, ModelPerson $person, int $eventId) {
        list($newScheduleData, $type) = $this->prepareData($data);
        $oldRows = $this->servicePersonSchedule->getTable()
            ->where('person_id', $person->person_id)
            ->where('schedule_item.schedule_group.event_id', $eventId)->where('schedule_item.schedule_group.schedule_group_type', $type);

        foreach ($oldRows as $row) {
            $modelPersonSchedule = ModelPersonSchedule::createFromActiveRow($row);
            if (in_array($modelPersonSchedule->schedule_item_id, $newScheduleData)) {
                // do nothing
                $index = array_search($modelPersonSchedule->schedule_item_id, $newScheduleData);
                unset($newScheduleData[$index]);
            } else {
                try {
                    $modelPersonSchedule->delete();
                } catch (\PDOException $exception) {
                    if (\preg_match('/payment/', $exception->getMessage())) {
                        throw new ExistingPaymentException(\sprintf(
                            _('Položka "%s" má už vygenerovanú platu, teda nejde zmazať.'),
                            $modelPersonSchedule->getScheduleItem()->getFullLabel()));
                    } else {
                        throw $exception;
                    }
                }
            }
        }

        foreach ($newScheduleData as $id) {
            $query = $this->serviceScheduleItem->findByPrimary($id);
            $modelScheduleItem = ModelScheduleItem::createFromActiveRow($query);
            if ($modelScheduleItem->getAvailableCapacity() > 0) {
                $this->servicePersonSchedule->createNewModel(['person_id' => $person->person_id, 'schedule_item_id' => $id]);
            } else {
                throw new FullCapacityException(sprintf(
                    _('Osobu %s nepodarilo ptihlásiť na program %s, z dôvodu plnej kapacity.'),
                    $person->getFullName(),
                    $modelScheduleItem->getFullLabel()
                ));
            }
        }
    }

    /**
     * @param ArrayHash $data
     * @return integer[]
     */
    private function prepareData(ArrayHash $data): array {
        foreach ($data as $type => $datum) {
            return [(array)json_decode($datum), $type];
        }
        return [];
    }
}

