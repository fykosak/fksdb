<?php

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\Models\ModelAddress;
use FKSDB\ORM\Models\ModelPostContact;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelMPostContact extends AbstractModelMulti {

    /**
     * @return \FKSDB\ORM\IModel|ModelAddress
     */
    public function getAddress() {
        return $this->getMainModel();
    }

    /**
     * @return \FKSDB\ORM\IModel|ModelPostContact
     */
    public function getPostContact() {
        return $this->getJoinedModel();
    }

}
