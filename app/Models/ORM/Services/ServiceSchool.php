<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ModelRegion;
use Fykosak\NetteORM\AbstractService;

class ServiceSchool extends AbstractService
{

    public function isCzSkSchool(?int $schoolId): bool
    {
        if (is_null($schoolId)) {
            return false;
        }
        /** @var ModelRegion|null $country */
        $country = $this->getTable()->select('address.region.country_iso')->where(['school_id' => $schoolId])->fetch();
        return in_array($country->country_iso, ['CZ', 'SK']);
    }
}
