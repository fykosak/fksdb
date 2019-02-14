<?php

namespace ORM\ModelsMulti\Events;

use AbstractModelMulti;


/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelMTsafParticipant extends AbstractModelMulti {

    /**
     * @return mixed
     */
    public function __toString() {
        if (!$this->getMainModel()->getPerson()) {
            trigger_error("Missing person in '" . $this->getMainModel() . "'.");
            //throw new InvalidStateException("Missing person in application ID '" . $this->getPrimary(false) . "'.");
        }
        return $this->getMainModel()->getPerson()->getFullname();
    }

}
