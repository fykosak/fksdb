<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Contestant;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Tests\Adapter;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-extends Adapter<ContestantModel,PersonModel>
 */
class ContestantToPersonAdapter extends Adapter
{

    protected function getModels(Model $model): iterable
    {
        return [$model->person];
    }

    protected function getLogPrepend(Model $model): string
    {
        return _('In person: ');
    }

    public function getId(): string
    {
        return 'person' . $this->test->getId();
    }
}
