<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Tests\Event\Team;

use FKSDB\Components\DataTest\TestLogger;
use FKSDB\Components\DataTest\TestMessage;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use FKSDB\Models\ORM\Tests\Test;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Fykosak\Utils\UI\Title;

/**
 * @phpstan-extends Test<TeamModel2>
 */
final class PendingTeams extends Test
{
    /**
     * @param TeamModel2 $model
     */
    protected function innerRun(TestLogger $logger, Model $model, string $id): void
    {
        if ($model->state->value === TeamState::Pending) {
            $logger->log(
                new TestMessage(
                    $id,
                    sprintf(
                        _('Team "%s"(%d) is still pending! Do something about it!'),
                        $model->name,
                        $model->fyziklani_team_id
                    ),
                    Message::LVL_WARNING
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
        return 'pendingTeams';
    }
}
