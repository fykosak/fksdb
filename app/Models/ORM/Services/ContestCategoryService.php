<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ContestCategoryModel;
use Fykosak\NetteORM\Service;

class ContestCategoryService extends Service
{

    public function findByLabel(string $label): ?ContestCategoryModel
    {
        return $this->getTable()->where('label', $label)->fetch();
    }
}
