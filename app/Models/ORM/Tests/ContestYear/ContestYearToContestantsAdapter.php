<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\ContestYear;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\ContestYearModel;
use FKSDB\Models\ORM\Tests\Adapter;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-extends Adapter<ContestYearModel,ContestantModel>
 */
class ContestYearToContestantsAdapter extends Adapter
{
    protected function getModels(Model $model): iterable
    {
        return $model->getContestants();//@phpstan-ignore-line
    }

    protected function getLogPrepend(Model $model): string
    {
        return sprintf(_('In contestant %s'), $model->person->getFullName());
    }

    public function getId(): string
    {
        return 'contestant' . $this->test->getId();
    }
}
