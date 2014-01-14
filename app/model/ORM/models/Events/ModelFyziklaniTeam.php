<?php

namespace ORM\Models\Events;

use AbstractModelSingle;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelFyziklaniTeam extends AbstractModelSingle {

    public function __toString() {
        return $this->name;
    }

}
