<?php

namespace FKSDB\Components\Forms\Controls\PersonAccommodation;

use FKSDB\Messages\Message;
use ModelEventAccommodation;
use ModelEventPersonAccommodation;
use ModelPerson;
use Nette\ArrayHash;
use Nette\NotImplementedException;
use ServiceEventPersonAccommodation;

class Handler {
    private $serviceEventPersonAccommodation;
    private $serviceEventAccommodation;

    public function __construct(ServiceEventPersonAccommodation $serviceEventPersonAccommodation, \ServiceEventAccommodation $serviceEventAccommodation) {
        $this->serviceEventPersonAccommodation = $serviceEventPersonAccommodation;
        $this->serviceEventAccommodation = $serviceEventAccommodation;
    }

    public function getAccommodationsByEventIdAncPersonId($personId, $eventId) {
        return $this->serviceEventPersonAccommodation->getTable()
            ->where('person_id', $personId)
            ->where('event_accommodation.event_id', $eventId);

    }

    /**
     * @param ArrayHash $data
     * @param ModelPerson $person
     * @param integer $eventId
     * @return Message[]
     */
    public function prepareAndUpdate(ArrayHash $data, ModelPerson $person, $eventId) {
        $messages = [];
        $oldRows = $this->getAccommodationsByEventIdAncPersonId($person->person_id, $eventId);

        $newAccommodationIds = $this->prepareData($data);

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
        foreach ($newAccommodationIds as $id) {
            $model = $this->serviceEventPersonAccommodation->createNew(['person_id' => $person->person_id, 'event_accommodation_id' => $id]);
            $query = $this->serviceEventAccommodation->findByPrimary($id);
            $eventAccommodation = ModelEventAccommodation::createFromTableRow($query);
            if ($eventAccommodation->getAvailableCapacity() > 0) {
                $this->serviceEventPersonAccommodation->save($model);
            } else {
                //$model->delete();
                $messages[] = new Message(sprintf(_('Osobu %s sa nepodarilo ubytovať na hotely "%s" v dni %s'),
                    $person->getFullName(),
                    $eventAccommodation->name,
                    $eventAccommodation->date->format(ModelEventAccommodation::ACC_DATE_FORMAT)
                ), Message::LVL_DANGER);
            }
        }
        return $messages;
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

