<?php

namespace FKSDB\Components\Forms\Controls\Schedule;

use FKSDB\ORM\Models\ModelEventAccommodation;
use FKSDB\ORM\Models\ModelEventPersonAccommodation;
use FKSDB\ORM\Models\ModelPerson;
use FKSDB\ORM\Services\Schedule\ServicePersonSchedule;
use FKSDB\ORM\Services\Schedule\ServiceScheduleGroup;
use FKSDB\ORM\Services\Schedule\ServiceScheduleItem;
use Nette\NotImplementedException;
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
/*
    public function prepareAndUpdate(ArrayHash $data, ModelPerson $person, $groupId) {

        $oldRows = $this->servicePersonSchedule->getTable()
            ->where('person_id', $person->person_id)
            ->where('event_accommodation.event_id', $eventId);

        $newAccommodationIds = $this->prepareData($data);

        foreach ($oldRows as $row) {
            $modelEventPersonAccommodation = ModelEventPersonAccommodation::createFromActiveRow($row);
            if (in_array($modelEventPersonAccommodation->event_accommodation_id, $newAccommodationIds)) {
                // do nothing
                $index = array_search($modelEventPersonAccommodation->event_accommodation_id, $newAccommodationIds);
                unset($newAccommodationIds[$index]);
            } else {
                try {
                    $modelEventPersonAccommodation->delete();
                } catch (\PDOException $exception) {
                    if (\preg_match('/payment_accommodation/', $exception->getMessage())) {
                        throw new ExistingPaymentException(\sprintf(
                            _('Položka "%s" má už vygenerovanú platu, teda nejde zmazať.'),
                            $modelEventPersonAccommodation->getLabel()));
                    } else {
                        throw $exception;
                    }
                }
            }
        }
        foreach ($newAccommodationIds as $id) {
            $model = $this->serviceEventPersonAccommodation->createNew(['person_id' => $person->person_id, 'event_accommodation_id' => $id]);
            $query = $this->serviceEventAccommodation->findByPrimary($id);
            $eventAccommodation = ModelEventAccommodation::createFromActiveRow($query);
            if ($eventAccommodation->getAvailableCapacity() > 0) {
                $this->serviceEventPersonAccommodation->save($model);
            } else {
                //$model->delete();
                throw new FullAccommodationCapacityException(sprintf(
                    _('Osobu %s sa nepodarilo ubytovať na hotely "%s" v dni %s'),
                    $person->getFullName(),
                    $eventAccommodation->name,
                    $eventAccommodation->date->format(ModelEventAccommodation::ACC_DATE_FORMAT)
                ));

            }
        }
    }*/

    /**
     * @param ArrayHash $data
     * @return integer[]
     */
    private function prepareData(ArrayHash $data): array {
        foreach ($data as $type => $datum) {
            switch ($type) {
                case MatrixField::RESOLUTION_ID:
                case SingleField::RESOLUTION_ID:
                case MultiHotelsField::RESOLUTION_ID:
                case MultiNightsField::RESOLUTION_ID:
                    $data = (array)json_decode($datum);
                    break;
                default:
                    throw new NotImplementedException(sprintf(_('Type "%s" is not implement.'), $type), 501);
            }
        }

        return array_values($data);
    }
}

