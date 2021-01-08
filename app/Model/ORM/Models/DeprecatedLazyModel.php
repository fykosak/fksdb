<?php

namespace FKSDB\Model\ORM\Models;

use Nette\DeprecatedException;

/**
 * Trait DDeprecatedLazyModel
 * @author Michal Červeňák <miso@fykos.cz>
 * Use for IModel that is lazy DB access not supported
 */
trait DeprecatedLazyModel {
    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function __set($key, $value) {
        throw new DeprecatedException();
    }
}
