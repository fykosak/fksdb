<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use Fykosak\NetteORM\Service;
use Fykosak\NetteORM\Model;

class ServicePersonHasFlag extends Service
{

    public function createNewModel(array $data): Model
    {
        $data['modified'] = new \DateTime();
        return parent::createNewModel($data);
    }

    public function updateModel(Model $model, array $data): bool
    {
        $data['modified'] = new \DateTime();
        return parent::updateModel($model, $data);
    }
}
