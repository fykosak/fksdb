<?php

namespace ORM\ModelsMulti\Events;

use AbstractModelMulti;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelMSousParticipant extends AbstractModelMulti {

    const STATE_AUTO_INVITED = 'auto.invited';
    const STATE_AUTO_SPARE = 'auto.spare';

    /**
     * @return mixed
     */
    public function __toString() {
        return $this->getMainModel()->getPerson()->getFullname();
    }

}
