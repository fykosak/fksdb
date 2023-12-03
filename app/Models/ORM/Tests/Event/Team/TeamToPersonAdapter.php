<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Event\Team;

use FKSDB\Models\ORM\Tests\Adapter;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-extends Adapter<TeamModel2,PersonModel>
 */
class TeamToPersonAdapter extends Adapter
{
    protected function getModels(Model $model): iterable
    {
        return $model->getPersons();
    }

    protected function getLogPrepend(Model $model): string
    {
        return sprintf(_('In person "%s"(%d): '), $model->getFullName(), $model->person_id);
    }

    public function getId(): string
    {
        return 'Person' . $this->test->getId();
    }
}
