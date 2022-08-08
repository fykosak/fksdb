<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\FlagModel;
use Fykosak\NetteORM\Service;

class FlagService extends Service
{

    public function findByFid(?string $fid): ?FlagModel
    {
        if (!$fid) {
            return null;
        }
        /** @var FlagModel $result */
        $result = $this->getTable()->where('fid', $fid)->fetch();
        return $result;
    }
}
