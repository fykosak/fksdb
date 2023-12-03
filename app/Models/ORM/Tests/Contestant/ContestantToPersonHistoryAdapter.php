<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Contestant;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Tests\Adapter;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-extends Adapter<ContestantModel,PersonHistoryModel>
 */
class ContestantToPersonHistoryAdapter extends Adapter
{

    protected function getModels(Model $model): iterable
    {
        return [$model->getPersonHistory()];
    }

    protected function getLogPrepend(Model $model): string
    {
        return _('In person related history');
    }

    public function getId(): string
    {
        return 'personHistory' . $this->test->getId();
    }
}
