<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\PersonHasFlagModel;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\Service;

/**
 * @phpstan-extends Service<PersonHasFlagModel>
 */
final class PersonHasFlagService extends Service
{
    public function storeModel(array $data, ?Model $model = null): PersonHasFlagModel
    {
        $data['modified'] = new \DateTime();
        return parent::storeModel($data, $model);
    }
}
