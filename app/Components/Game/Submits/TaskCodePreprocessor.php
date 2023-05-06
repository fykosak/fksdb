<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;

final class TaskCodePreprocessor
{
    /**
     * @throws TaskCodeException
     */
    public static function getTeam(string $code, EventModel $event): TeamModel2
    {
        $teamId = self::extractTeamId($code);
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
        $taskLabel = self::extractTaskLabel($code);
        if ($taskLabel === 'XX') {
            throw new NoTaskLeftException();
        }
        /** @var TaskModel $task */
        $task = $event->getTasks()->where('label', $taskLabel)->fetch();
        if (!$task) {
            throw new TaskCodeException(\sprintf(_('Task %s does not exists.'), $taskLabel));
        }
        return $task;
    }

    private static function extractTeamId(string $code): int
    {
        $fullCode = self::createFullNumCode($code);
        return (int)substr($fullCode, 0, 6);
    }

    private static function extractTaskLabel(string $code): string
    {
        $fullCode = self::createFullNumCode($code);
        return (string)substr($fullCode, 6, 2);
    }

    /**
     * @throws TaskCodeException
     */
    private static function createFullNumCode(string $code): string
    {
        $length = strlen($code);
        if ($length > 9) {
            throw new TaskCodeException(_('Code is too long'));
        }

        $fullCode = str_repeat('0', 9 - $length) . strtoupper($code);
        if (strlen($fullCode) != 9) {
            throw new TaskCodeException(_('Code is too short'));
        }
        $subCode = str_split(
            str_replace(['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'X'], [1, 2, 3, 4, 5, 6, 7, 8, 0], $code)
        );

        $sum = 3 * ($subCode[0] + $subCode[3] + $subCode[6])
            + 7 * ($subCode[1] + $subCode[4] + $subCode[7])
            + ($subCode[2] + $subCode[5] + $subCode[8]);
        if ($sum % 10 !== 0) {
            throw new ControlMismatchException();
        }
        return $fullCode;
    }
}
