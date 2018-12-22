<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;

use FKSDB\ORM\ModelEventAccommodation;
use FKSDB\ORM\ModelEventPersonAccommodation;
use FKSDB\ORM\ModelPerson;
use Nette\ArrayHash;
use Nette\NotImplementedException;
use ServiceEventPersonAccommodation;
use Submits\StorageException;

class Handler {
    private $serviceEventPersonAccommodation;
    private $serviceEventAccommodation;

    public function __construct(ServiceEventPersonAccommodation $serviceEventPersonAccommodation, \ServiceEventAccommodation $serviceEventAccommodation) {
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
        $this->serviceEventAccommodation = $serviceEventAccommodation;
    }

    /**
     * @param ArrayHash $data
     * @param ModelPerson $person
     * @param integer $eventId
     * @throws StorageException
     * @return void
     */
    public function prepareAndUpdate(ArrayHash $data, ModelPerson $person, $eventId) {
        $oldRows = $this->serviceEventPersonAccommodation->getTable()->where('person_id', $person->person_id)->where('event_accommodation.event_id', $eventId);

        $newAccommodationIds = $this->prepareData($data);

        foreach ($oldRows as $row) {
            $modelEventPersonAccommodation = ModelEventPersonAccommodation::createFromTableRow($row);
            if (in_array($modelEventPersonAccommodation->event_accommodation_id, $newAccommodationIds)) {
                // do nothing
                $index = array_search($modelEventPersonAccommodation->event_accommodation_id, $newAccommodationIds);
                unset($newAccommodationIds[$index]);
            } else {
                $modelEventPersonAccommodation->delete();
            }
        }
        foreach ($newAccommodationIds as $id) {
            $model = $this->serviceEventPersonAccommodation->createNew(['person_id' => $person->person_id, 'event_accommodation_id' => $id]);
            $query = $this->serviceEventAccommodation->findByPrimary($id);
            $eventAccommodation = ModelEventAccommodation::createFromTableRow($query);
            if ($eventAccommodation->getAvailableCapacity() > 0) {
                $this->serviceEventPersonAccommodation->save($model);
            } else {
                //$model->delete();
                throw new StorageException(sprintf(
                    _('Osobu %s sa nepodarilo ubytovať na hotely "%s" v dni %s'),
                    $person->getFullName(),
                    $eventAccommodation->name,
                    $eventAccommodation->date->format(ModelEventAccommodation::ACC_DATE_FORMAT)
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
            switch ($type) {
                case MatrixField::RESOLUTION_ID:
                case SingleField::RESOLUTION_ID:
                case MultiHotelsField::RESOLUTION_ID:
                case MultiNightsField::RESOLUTION_ID:
                    $data = (array)json_decode($datum);
                    break;
                default:
                    throw new NotImplementedException(sprintf(_('Type "%s" is not implement.'), $type));
            }
        }

        return array_values($data);
    }
}

