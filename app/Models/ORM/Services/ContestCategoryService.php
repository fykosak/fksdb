<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\ContestCategoryModel;
use Fykosak\NetteORM\Service;

/**
 * @phpstan-extends Service<ContestCategoryModel>
 */
final class ContestCategoryService extends Service
{

    public function findByLabel(string $label): ?ContestCategoryModel
    {
        /** @var ContestCategoryModel|null $contestCategory */
        $contestCategory = $this->getTable()->where('label', $label)->fetch();
        return $contestCategory;
    }
}
