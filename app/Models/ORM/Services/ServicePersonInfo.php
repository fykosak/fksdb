<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\ModelPersonInfo;
use Fykosak\NetteORM\Service;

/**
 * @method ModelPersonInfo findByPrimary($key)
 */
class ServicePersonInfo extends Service
{
    public function createNewModel(array $data): ModelPersonInfo
    {
        if (isset($data['agreed']) && $data['agreed'] == '1') {
            $data['agreed'] = new \DateTime();
        }
        return parent::createNewModel($data);
    }

    /**
     * @param ModelPersonInfo $model
     * @throws ModelException
     */
    public function updateModel(Model $model, array $data): bool
    {
        if (isset($data['agreed'])) {
            if ($data['agreed'] == '1') {
                $data['agreed'] = new \DateTime();
            } elseif ($data['agreed'] == '0') {
                unset($data['agreed']);
            }
        }
        return parent::updateModel($model, $data);
    }
}
