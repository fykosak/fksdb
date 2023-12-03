<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Event;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Tests\Adapter;
use Fykosak\NetteORM\Model\Model;

/**
 * @phpstan-extends Adapter<EventModel,TeamModel2>
 */
class TeamAdapter extends Adapter
{
    protected function getModels(Model $model): iterable
    {
        return $model->getTeams(); // @phpstan-ignore-line
    }

    protected function getLogPrepend(Model $model): string
    {
        return sprintf(_('In team "%s"(%d): '), $model->name, $model->fyziklani_team_id);
    }

    public function getId(): string
    {
        return 'Team' . $this->test->getId();
    }
}
