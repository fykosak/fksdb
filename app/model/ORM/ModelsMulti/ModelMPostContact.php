<?php

namespace FKSDB\ORM\ModelsMulti;

use FKSDB\ORM\AbstractModelMulti;
use FKSDB\ORM\IModel;
use FKSDB\ORM\Models\ModelAddress;
use FKSDB\ORM\Models\ModelPostContact;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelMPostContact extends AbstractModelMulti {

    /**
     * @return IModel|ModelAddress
     */
    public function getAddress(): ModelAddress {
        return $this->getMainModel();
    }

    /**
     * @return IModel|ModelPostContact
     */
    public function getPostContact(): ModelPostContact {
        return $this->getJoinedModel();
    }
}
