<?php

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelGlobalSession extends AbstractModelSingle {

    public function isValid() {
        $now = time();
        return ($this->until->getTimestamp() >= $now) && ($this->since->getTimestamp() <= $now);
    }

}
