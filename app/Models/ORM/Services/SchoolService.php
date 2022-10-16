<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\RegionModel;
use FKSDB\Models\ORM\Models\SchoolModel;
use Fykosak\NetteORM\Service;

/**
 * @method SchoolModel storeModel(array $data, ?SchoolModel $model = null)
 * @method SchoolModel findByPrimary($key)
 */
class SchoolService extends Service
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
