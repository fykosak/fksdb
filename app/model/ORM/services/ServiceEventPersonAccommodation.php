<?php

class ServiceEventPersonAccommodation extends \AbstractServiceSingle {
    protected $tableName = DbNames::TAB_EVENT_PERSON_ACCOMMODATION;
    protected $modelClassName = 'ModelEventPersonAccommodation';

    public function prepareAndUpdate(\Nette\ArrayHash $data, ModelPerson $person, $eventId) {
        foreach ($data as $type => $datum) {
            switch ($type) {
                case 'matrix':
                    $data = (array)json_decode($datum);
                    break;
                default:
                    throw new \Nette\NotImplementedException("Type $type is not implement");
            }
        }
        $oldRows = $this->getTable()->where('person_id', $person->person_id)->where('event_accommodation.event_id', $eventId);
        $newAccommodationIds = array_values($data);
        \Nette\Diagnostics\Debugger::barDump($newAccommodationIds);
        /**
         * @var $row ModelEventPersonAccommodation
         */
        foreach ($oldRows as $row) {
            if (in_array($row->event_accommodation_id, $newAccommodationIds)) {
                // do nothing
                \Nette\Diagnostics\Debugger::barDump('do nothing');
                $index = array_search($row->event_accommodation_id, $newAccommodationIds);
                unset($newAccommodationIds[$index]);
            } else {
                $row->delete();
                \Nette\Diagnostics\Debugger::barDump('delete');
            }

        }
        \Nette\Diagnostics\Debugger::barDump($newAccommodationIds);
        foreach ($newAccommodationIds as $id) {
            $this->createNew(['person_id' => $person->person_id, 'event_accommodation_id' => $id]);
            \Nette\Diagnostics\Debugger::barDump('createNew');
        }

    }

}
