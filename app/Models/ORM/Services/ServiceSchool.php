<?php

namespace FKSDB\Models\ORM\Services;



use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\ModelRegion;
use FKSDB\Models\ORM\Models\ModelSchool;
use FKSDB\Models\ORM\Tables\TypedTableSelection;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ServiceSchool extends AbstractServiceSingle {


    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_SCHOOL, ModelSchool::class);
    }

    public function getSchools(): TypedTableSelection {
        return $this->getTable()
            ->select(DbNames::TAB_SCHOOL . '.*')
            ->select(DbNames::TAB_ADDRESS . '.*');
    }

    public function isCzSkSchool(?int $schoolId): bool {
        if (is_null($schoolId)) {
            return false;
        }
        /** @var ModelRegion|false $country */
        $country = $this->getTable()->select('address.region.country_iso')->where(['school_id' => $schoolId])->fetch();
        return in_array($country->country_iso, ['CZ', 'SK']);
    }
}
