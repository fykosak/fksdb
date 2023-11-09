<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\Event;

use FKSDB\Components\DataTest\Tests\Adapter;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\NetteORM\Model;

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
