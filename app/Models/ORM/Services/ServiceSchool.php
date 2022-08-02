<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\RegionModel;
use Fykosak\NetteORM\Service;

class ServiceSchool extends Service
{

    public function isCzSkSchool(?int $schoolId): bool
    {
        if (is_null($schoolId)) {
            return false;
        }
        /** @var RegionModel|null $country */
        $country = $this->getTable()->select('address.region.country_iso')->where(['school_id' => $schoolId])->fetch();
        return in_array($country->country_iso, ['CZ', 'SK']);
    }
}
