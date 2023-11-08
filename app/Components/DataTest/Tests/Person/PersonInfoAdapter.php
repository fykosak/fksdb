<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\Person;

use FKSDB\Components\DataTest\Adapter;
use FKSDB\Models\ORM\Models\PersonInfoModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model;

/**
 * @phpstan-extends Adapter<PersonModel,PersonInfoModel>
 */
class PersonInfoAdapter extends Adapter
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
}
