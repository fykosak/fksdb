<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use Fykosak\NetteORM\AbstractService;
use Fykosak\NetteORM\AbstractModel;

class ServicePersonHasFlag extends AbstractService
{

    public function createNewModel(array $data): AbstractModel
    {
        $data['modified'] = new \DateTime();
        return parent::createNewModel($data);
    }

    public function updateModel(AbstractModel $model, array $data): bool
    {
        $data['modified'] = new \DateTime();
        return parent::updateModel($model, $data);
    }
}
