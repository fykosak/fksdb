<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelRegion;
use Nette\Database\Table\Selection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceRegion extends AbstractServiceSingle {

    /**
     * @return string
     */
    protected function getModelClassName(): string {
        return ModelRegion::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_REGION;
    }

    /**
     * @return Selection
     */
    public function getCountries(): Selection {
        return $this->getTable()->where('country_iso = nuts');
    }

}

