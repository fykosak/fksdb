<?php

namespace ORM\ModelsMulti\Events;

use FKSDB\ORM\AbstractModelMulti;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelMVikendParticipant extends AbstractModelMulti {

    /**
     * @return mixed
     */
    public function __toString() {
        return $this->getMainModel()->getPerson()->getFullname();
    }

}
