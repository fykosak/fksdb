<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelRegion;
use FKSDB\ORM\Tables\TypedTableSelection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceRegion extends AbstractServiceSingle {

    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelRegion::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_REGION;
    }

    /**
     * @return TypedTableSelection
     */
    public function getCountries(): TypedTableSelection {
        return $this->getTable()->where('country_iso = nuts');
    }
}
