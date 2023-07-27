<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\EventOrgModel;
use Fykosak\NetteORM\Service;

/**
 * @method EventOrgModel storeModel(array $data, ?EventOrgModel $model = null)
 * @method EventOrgModel|null findByPrimary($key)
 */
final class EventOrgService extends Service
{
}
