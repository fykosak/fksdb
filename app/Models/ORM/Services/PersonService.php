<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services;

use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model\Model;
use Fykosak\NetteORM\Service\Service;

/**
 * @phpstan-extends Service<PersonModel>
 */
final class PersonService extends Service
{
    public function findByEmail(?string $email): ?PersonModel
    {
        /** @var PersonModel|null $person */
        $person = $email ? $this->getTable()->where(':person_info.email', $email)->fetch() : null;
        return $person;
    }

    /**
     * @phpstan-param array{gender?:string|null,family_name:string} $data
     */
    public function storeModel(array $data, ?Model $model = null): PersonModel
    {
        if (!isset($data['gender']) && !isset($model)) {
            $data['gender'] = PersonModel::inferGender($data);
        }
        return parent::storeModel($data, $model);
    }
}
