<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Warehouse;

use FKSDB\Models\ORM\Models\Warehouse\WarehouseItemVariantModel;
use Fykosak\NetteORM\Selection\TypedSelection;
use Fykosak\NetteORM\Service\Service;

/**
 * @phpstan-extends Service<WarehouseItemVariantModel>
 */
final class WarehouseItemVariantService extends Service
{
    /**
     * @return TypedSelection<WarehouseItemVariantModel>
     */
    public function findByFingerprint(string $fingerprint): TypedSelection
    {
        return $this->getTable()->where('fingerprint', $fingerprint);
    }

    protected function filterData(array $data): array
    {
        $result = parent::filterData($data);
        unset($result['fingerprint']);
        return $result;
    }
}
