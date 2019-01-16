<?php


namespace FKSDB\Transitions;


use FKSDB\ORM\ModelEvent;

interface IEventReferencedModel {

    public function getEvent(): ModelEvent;
}
