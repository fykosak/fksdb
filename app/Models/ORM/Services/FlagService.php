<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\FlagModel;
use Fykosak\NetteORM\Service;

/**
 * @phpstan-extends Service<FlagModel>
 */
final class FlagService extends Service
{

    public function findByFid(?string $fid): ?FlagModel
    {
        return $fid ? $this->getTable()->where('fid', $fid)->fetch() : null;
    }
}
