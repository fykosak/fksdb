<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Person;

use FKSDB\Models\ORM\Models\PersonInfoModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Tests\Adapter;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-extends Adapter<PersonModel,PersonInfoModel>
 */
final class PersonInfoAdapter extends Adapter
{
    protected function getModels(Model $model): iterable
    {
        $info = $model->getInfo();
        if (!$info) {
            return [];
        }
        return [$info];
    }

    protected function getLogPrepend(Model $model): string
    {
        return '';
    }

    public function getId(): string
    {
        return 'personToInfo';
    }
}
