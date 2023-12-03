<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Person;

use FKSDB\Models\ORM\Tests\Adapter;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-extends Adapter<PersonModel,PersonHistoryModel>
 */
class PersonHistoryAdapter extends Adapter
{
    /**
     * @param PersonModel $model
     */
    protected function getModels(Model $model): iterable
    {
        return $model->getHistories();//@phpstan-ignore-line
    }

    /**
     * @param PersonHistoryModel $model
     */
    protected function getLogPrepend(Model $model): string
    {
        return sprintf(_('In ac. year %d/%d: '), $model->ac_year, $model->ac_year + 1);
    }

    public function getId(): string
    {
        return 'PersonHistory' . $this->test->getId();
    }
}
