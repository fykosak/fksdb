<?php


namespace FKSDB\Transitions;


use FKSDB\ORM\Models\ModelEvent;

/**
 * Interface IEventReferencedModel
 * @package FKSDB\Transitions
 */
interface IEventReferencedModel {

    /**
     * @return \FKSDB\ORM\Models\ModelEvent
     */
    public function getEvent(): ModelEvent;
}
