<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use Nette\DeprecatedException;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @deprecated
 */
class ServiceSpamee extends AbstractServiceSingle {

    /**
     * @return string
     * @deprecated
     */
    protected function getModelClassName(): string {
        throw new DeprecatedException();
    }

    /**
     * @return string
     * @deprecated
     */
    protected function getTableName(): string {
        throw new DeprecatedException();
    }
}

