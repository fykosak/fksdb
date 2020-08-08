<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\DeprecatedLazyDBTrait;
use FKSDB\ORM\Models\ModelRegion;
use FKSDB\ORM\Models\ModelSchool;
use FKSDB\ORM\Tables\TypedTableSelection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceSchool extends AbstractServiceSingle {
    use DeprecatedLazyDBTrait;

    public function getModelClassName(): string {
        return ModelSchool::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_SCHOOL;
    }

    public function getSchools(): TypedTableSelection {
        return $this->getTable()
            ->select(DbNames::TAB_SCHOOL . '.*')
            ->select(DbNames::TAB_ADDRESS . '.*');
    }

    public function isCzSkSchool(int $schoolId): bool {
        /** @var ModelRegion|false $country */
        $country = $this->getTable()->select('address.region.country_iso')->where(['school_id' => $schoolId])->fetch();
        return in_array($country->country_iso, ['CZ', 'SK']);
    }
}
