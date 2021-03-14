<?php

namespace FKSDB\Components\Forms\Controls\Schedule;

use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Models\Schedule\ModelPersonSchedule;
use FKSDB\Models\ORM\Models\Schedule\ModelScheduleItem;
use FKSDB\Models\ORM\Services\Schedule\ServicePersonSchedule;
use FKSDB\Models\ORM\Services\Schedule\ServiceScheduleGroup;
use FKSDB\Models\ORM\Services\Schedule\ServiceScheduleItem;
use Nette\Utils\ArrayHash;
use PDOException;

/**
 * Class Handler
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Handler {

    private ServiceScheduleGroup $serviceScheduleGroup;
    private ServicePersonSchedule $servicePersonSchedule;
    private ServiceScheduleItem $serviceScheduleItem;

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
     * @param ModelEvent $event
     * @return void
     * @throws ExistingPaymentException
     * @throws FullCapacityException
     * @throws NotImplementedException
     */
    public function prepareAndUpdate(ArrayHash $data, ModelPerson $person, ModelEvent $event): void {
        foreach ($this->prepareData($data) as $type => $newScheduleData) {
            $this->updateDataType($newScheduleData, $type, $person, $event);
        }
    }

    /**
     * @param array $newScheduleData
     * @param string $type
     * @param ModelPerson $person
     * @param ModelEvent $event
     * @return void
     * @throws ExistingPaymentException
     * @throws FullCapacityException
     * @throws NotImplementedException
     * @throws PDOException
     * @throws ModelException
     */
    private function updateDataType(array $newScheduleData, string $type, ModelPerson $person, ModelEvent $event): void {
        $oldRows = $this->servicePersonSchedule->getTable()
            ->where('person_id', $person->person_id)
            ->where('schedule_item.schedule_group.event_id', $event->event_id)->where('schedule_item.schedule_group.schedule_group_type', $type);

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
                            _('The item "%s" has already a payment generated, so it cannot be deleted.'),
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
                    _('The person %s could not be registered for "%s" because of full capacity.'),
                    $person->getFullName(),
                    $modelScheduleItem->getLabel()
                ));
            }
        }
    }

    private function prepareData(ArrayHash $data): array {
        $newData = [];
        foreach ($data as $type => $datum) {
            $newData[$type] = \array_values((array)\json_decode($datum));
        }
        return $newData;
    }
}
