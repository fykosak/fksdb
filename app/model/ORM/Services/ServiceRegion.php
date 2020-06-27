<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyDBTrait;
use FKSDB\ORM\Models\ModelRegion;
use FKSDB\ORM\Tables\TypedTableSelection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceRegion extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    public function getModelClassName(): string {
        return ModelRegion::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_REGION;
    }

    public function getCountries(): TypedTableSelection {
        return $this->getTable()->where('country_iso = nuts');
    }
}
