<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;

final class TaskCodePreprocessor
{
    public static function checkControlNumber(string $code): bool
    {
        if (strlen($code) != 9) {
            return false;
        }
        $subCode = str_split(self::getNumLabel($code));

        $sum = 3 * ($subCode[0] + $subCode[3] + $subCode[6])
            + 7 * ($subCode[1] + $subCode[4] + $subCode[7])
            + ($subCode[2] + $subCode[5] + $subCode[8]);
        return $sum % 10 == 0;
    }

    public static function extractTeamId(string $code): int
    {
        return (int)substr($code, 0, 6);
    }

    public static function extractTaskLabel(string $code): string
    {
        return (string)substr($code, 6, 2);
    }

    public static function getNumLabel(string $code): string
    {
        return str_replace(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'], [1, 2, 3, 4, 5, 6, 7, 8], $code);
    }

    /**
     * @throws TaskCodeException
     */
    public static function createFullCode(string $code): string
    {
        $length = strlen($code);
        if ($length > 9) {
            throw new TaskCodeException(_('Code is too long'));
        }

        return str_repeat('0', 9 - $length) . strtoupper($code);
    }

    /**
     * @throws TaskCodeException
     */
    public static function getTeam(string $code, EventModel $event): TeamModel2
    {
        $teamId = self::extractTeamId(self::createFullCode($code));
        /** @var TeamModel2 $team */
        $team = $event->getTeams()->where('fyziklani_team_id', $teamId)->fetch();
        if (!$team) {
            throw new TaskCodeException(\sprintf(_('Team %s does not exists.'), $teamId));
        }
        return $team;
    }

    /**
     * @throws TaskCodeException
     */
    public static function getTask(string $code, EventModel $event): TaskModel
    {
        /* correct label */
        $taskLabel = self::extractTaskLabel(self::createFullCode($code));
        /** @var TaskModel $task */
        $task = $event->getTasks()->where('label', $taskLabel)->fetch();
        if (!$task) {
            throw new TaskCodeException(\sprintf(_('Task %s does not exists.'), $taskLabel));
        }
        return $task;
    }
}
