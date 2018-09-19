<?php

namespace ORM\ModelsMulti\Events;

use AbstractModelMulti;


/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelMDsefParticipant extends AbstractModelMulti {

    public function __toString() {
        if (!$this->getMainModel()->getPerson()) {
            trigger_error("Missing person in '" . $this->getMainModel() . "'.");
            //throw new InvalidStateException("Missing person in '" . $this->getMainModel() . "'.");
        }
        return $this->getMainModel()->getPerson()->getFullname();
    }

}
