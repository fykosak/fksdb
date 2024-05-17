<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Spam;

use FKSDB\Models\ORM\Models\Spam\SpamSchoolModel;
use Fykosak\NetteORM\Service\Service;

/**
 * @phpstan-extends Service<SpamSchoolModel>
 */
final class SpamSchoolService extends Service
{
    public function exists(string $label): bool
    {
        return $this->getTable()->wherePrimary($label)->count() > 0;
    }
}
