<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\SchoolLabelModel;
use Fykosak\NetteORM\Service\Service;

/**
 * @phpstan-extends Service<SchoolLabelModel>
 */
final class SchoolLabelService extends Service
{
    public function findByLabel(string $label): ?SchoolLabelModel
    {
        /** @var SchoolLabelModel|null $schoolLabel */
        $schoolLabel = $this->getTable()->where('school_label_key', $label)->fetch();
        return $schoolLabel;
    }

    public function exists(string $label): bool
    {
        return !is_null($this->findByLabel($label));
    }
}
