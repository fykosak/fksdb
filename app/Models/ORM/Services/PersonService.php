<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use Fykosak\NetteORM\Model;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Service;

/**
 * @method PersonModel|null findByPrimary($key)
 * @method PersonModel storeModel(array $data, ?PersonModel $model = null)
 */
class PersonService extends Service
{

    public function findByEmail(?string $email): ?PersonModel
    {
        if (!$email) {
            return null;
        }
        /** @var PersonModel|null $result */
        $result = $this->getTable()->where(':person_info.email', $email)->fetch();
        return $result;
    }

    public function createNewModel(array $data): PersonModel
    {
        if (is_null($data['gender'])) {
            $data['gender'] = PersonModel::inferGender($data);
        }
        return parent::createNewModel($data);
    }
}
