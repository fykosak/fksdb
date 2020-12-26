<?php

namespace FKSDB\Models\ORM\ModelsMulti;

use FKSDB\Models\ORM\Models\ModelAddress;
use FKSDB\Models\ORM\Models\ModelPostContact;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @method ModelAddress getMainModel()
 * @method ModelPostContact getJoinedModel()
 */
class ModelMPostContact extends AbstractModelMulti {

    public function getAddress(): ModelAddress {
        return $this->getMainModel();
    }

    public function getPostContact(): ModelPostContact {
        return $this->getJoinedModel();
    }
}
