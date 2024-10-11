<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Warehouse;

use Fykosak\NetteORM\Service\Service;
use FKSDB\Models\ORM\Models\Warehouse\ItemModel;
use Fykosak\NetteORM\Selection\TypedSelection;

/**
 * @phpstan-extends Service<ItemModel>
 */
final class ItemService extends Service
{
    /**
     * @param string $fingerprint
     * @return TypedSelection
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
