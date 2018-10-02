<?php

use FKSDB\ORM\ModelAddress;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelMPostContact extends AbstractModelMulti {

    /**
     * @return ModelAddress
     */
    public function getAddress() {
        return $this->getMainModel();
    }

    /**
     * @return ModelPostContact
     */
    public function getPostContact() {
        return $this->getJoinedModel();
    }

}
