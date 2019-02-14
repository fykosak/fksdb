<?php


namespace FKSDB\Transitions;


use FKSDB\ORM\ModelEvent;

/**
 * Interface IEventReferencedModel
 * @package FKSDB\Transitions
 */
interface IEventReferencedModel {

    /**
     * @return ModelEvent
     */
    public function getEvent(): ModelEvent;
}
