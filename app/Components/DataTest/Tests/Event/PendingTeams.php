<?php

declare(strict_types=1);

namespace FKSDB\Components\DataTest\Tests\Event;

use FKSDB\Components\DataTest\Tests\Test;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<EventModel>
 */
class PendingTeams extends Test
{
    /**
     * @param EventModel $model
     */
    public function run(Logger $logger, Model $model): void
    {
        $teams = $model->getTeams()->where('state', TeamState::Pending);
        /** @var TeamModel2 $team */
        foreach ($teams as $team) {
            $logger->log(
                new Message(
                    sprintf(
                        _('Team "%s"(%d) is still pending! Do something about it!'),
                        $team->name,
                        $team->fyziklani_team_id
                    ),
                    Message::LVL_ERROR
                )
            );
        }
    }

    public function getTitle(): Title
    {
        return new Title(null, _('Pending teams'), 'fas fa-poo');
    }

    public function getId(): string
    {
        return 'EventPendingTeams';
    }
}
