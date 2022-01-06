<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ModelFlag;
use Fykosak\NetteORM\AbstractService;

class ServiceFlag extends AbstractService {

    public function findByFid(?string $fid): ?ModelFlag {
        if (!$fid) {
            return null;
        }
        /** @var ModelFlag $result */
        $result = $this->getTable()->where('fid', $fid)->fetch();
        return $result;
    }
}
