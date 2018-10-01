<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;

use FKSDB\Messages\Message;
use ModelEventAccommodation;
use ModelEventPersonAccommodation;
use ModelPerson;
use Nette\ArrayHash;
use Nette\Database\Table\Selection;
use Nette\NotImplementedException;
use ServiceEventPersonAccommodation;

class Handler {
    private $serviceEventPersonAccommodation;
    private $serviceEventAccommodation;

    public function __construct(ServiceEventPersonAccommodation $serviceEventPersonAccommodation, \ServiceEventAccommodation $serviceEventAccommodation) {
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
        $this->serviceEventAccommodation = $serviceEventAccommodation;
    }

    public function getAccommodationsByEventIdAndPersonId($personId, $eventId) {
        return $this->serviceEventPersonAccommodation->getTable()
            ->where('person_id', $personId)
            ->where('event_accommodation.event_id', $eventId);
    }

    /**
     * @param ArrayHash $data
     * @param ModelPerson $person
     * @param integer $eventId
     * @throws CapacityException
     * @return void
     */
    public function prepareAndUpdate(ArrayHash $data, ModelPerson $person, $eventId) {
        // get old rows
        $oldRows = $this->getAccommodationsByEventIdAndPersonId($person->person_id, $eventId);
        // get new data
        $newAccommodationIds = $this->prepareData($data);
        // remove or do noting where acc in active again
        $this->checkCurrentAccommodation($oldRows, $newAccommodationIds);
        foreach ($newAccommodationIds as $id) {
            // register new acc
            $this->createNewAccommodationDatum($person, $id);
        }
    }

    private function checkCurrentAccommodation(Selection $oldRows, &$newAccommodationIds) {
        foreach ($oldRows as $row) {
            $model = ModelEventPersonAccommodation::createFromTableRow($row);
            if (in_array($model->event_accommodation_id, $newAccommodationIds)) {
                // do nothing
                $index = array_search($model->event_accommodation_id, $newAccommodationIds);
                unset($newAccommodationIds[$index]);
            } else {
                $row->delete();
            }
        }
    }

    /**
     * @param ModelPerson $person
     * @param $accommodationId
     * @throws CapacityException
     */
    private function createNewAccommodationDatum(ModelPerson $person, $accommodationId) {

        $model = $this->serviceEventPersonAccommodation->createNew([
            'person_id' => $person->person_id,
            'event_accommodation_id' => $accommodationId,
        ]);

        $query = $this->serviceEventAccommodation->findByPrimary($accommodationId);
        $eventAccommodation = ModelEventAccommodation::createFromTableRow($query);
        if ($eventAccommodation->getAvailableCapacity() > 0) {
            $this->serviceEventPersonAccommodation->save($model);
        } else {
            //$model->delete();
            throw new CapacityException(sprintf(_('Osobu %s sa nepodarilo ubytovať na hotely "%s" v dni %s'),
                $person->getFullName(),
                $eventAccommodation->name,
                $eventAccommodation->date->format(ModelEventAccommodation::ACC_DATE_FORMAT)
            ));
        }

    }

    /**
     * @param ArrayHash $data
     * @return integer[]
     */
    private function prepareData(ArrayHash $data) {
        foreach ($data as $type => $datum) {
            switch ($type) {
                case Matrix::RESOLUTION_ID:
                    return array_values((array)json_decode($datum));
                default:
                    throw new NotImplementedException(sprintf(_('Type "%s" is not implement.'), $type));
            }
        }
        return [];
    }
}

