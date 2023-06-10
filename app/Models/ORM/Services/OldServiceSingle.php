<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use Fykosak\NetteORM\Service;
use Fykosak\NetteORM\Model;

/**
 * Service class to high-level manipulation with ORM objects.
 * Use singleton descendant implementations.
 *
 * @use Service
 * @method Model storeModel(array $data, ?Model $model = null)
 */
abstract class OldServiceSingle extends Service
{

    /**
     * @deprecated
     * @internal Used also in MultiTableSelection.
     */
    public function createFromArray(array $data): Model
    {
        $className = $this->getModelClassName();
        $data = $this->filterData($data);
        return new $className($data, $this->getTable());
    }
}
