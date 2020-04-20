<?php

namespace FKSDB\ORM\Services;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelRegion;
use FKSDB\ORM\Models\ModelSchool;
use FKSDB\ORM\Tables\TypedTableSelection;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceSchool extends AbstractServiceSingle {

    /**
     * @return string
     */
    public function getModelClassName(): string {
        return ModelSchool::class;
    }

    /**
     * @return string
     */
    protected function getTableName(): string {
        return DbNames::TAB_SCHOOL;
    }

    /**
     * @return TypedTableSelection
     */
    public function getSchools(): TypedTableSelection {
        return $this->getTable()
            ->select(DbNames::TAB_SCHOOL . '.*')
            ->select(DbNames::TAB_ADDRESS . '.*');
    }

    /**
     * @param int $schoolId
     * @return bool
     */
    public function isCzSkSchool(int $schoolId): bool {
        /** @var ModelRegion|false $country */
        $country = $this->getTable()->select('address.region.country_iso')->where(['school_id' => $schoolId])->fetch();
        return in_array($country->country_iso, ['CZ', 'SK']);
    }
}
