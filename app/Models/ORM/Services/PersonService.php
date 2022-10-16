<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\Service;

/**
 * @method PersonModel|null findByPrimary($key)
 */
class PersonService extends Service
{
    public function findByEmail(?string $email): ?PersonModel
    {
        return $email ? $this->getTable()->where(':person_info.email', $email)->fetch() : null;
    }

    public function storeModel(array $data, ?Model $model = null): PersonModel
    {
        if (is_null($data['gender']) && !isset($model)) {
            $data['gender'] = PersonModel::inferGender($data);
        }
        return parent::storeModel($data, $model);
    }
}
