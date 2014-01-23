<?php

namespace ORM\ModelsMulti\Events;

use AbstractModelMulti;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelMDsefParticipant extends AbstractModelMulti {

    public function __toString() {
        return $this->getMainModel()->getPerson()->getFullname();
    }

}
