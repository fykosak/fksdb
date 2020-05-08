<?php

namespace FKSDB\ORM\ModelsMulti\Events;

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\ModelEvent;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelMDsefParticipant extends AbstractModelMulti implements IEventReferencedModel {

    /**
     * @return mixed
     */
    public function __toString() {
        if (!$this->getMainModel()->getPerson()) {
            trigger_error("Missing person in '" . $this->getMainModel() . "'.");
            //throw new InvalidStateException("Missing person in '" . $this->getMainModel() . "'.");
        }
        return $this->getMainModel()->getPerson()->getFullname();
    }

    public function getEvent(): ModelEvent {
        return $this->getMainModel()->getEvent();
    }
}
