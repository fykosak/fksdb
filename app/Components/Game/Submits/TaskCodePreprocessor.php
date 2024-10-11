<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;

final class TaskCodePreprocessor
{
    private EventModel $event;

    public function __construct(EventModel $event)
    {
        $this->event = $event;
    }

    /**
     * @throws TaskCodeException
     */
    public function getTeam(string $code): TeamModel2
    {
        $teamId = self::extractTeamId($code);
        /** @var TeamModel2|null $team */
        $team = $this->event->getTeams()->where('fyziklani_team_id', $teamId)->fetch();
        if (!$team) {
            throw new TaskCodeException(\sprintf(_('Team %s does not exist.'), $teamId));
        }
        return $team;
    }

    /**
     * @throws TaskCodeException
     * @throws NoTaskLeftException
     */
    public function getTask(string $code): TaskModel
    {
        $taskLabel = self::extractTaskLabel($code);
        if ($taskLabel === 'XX') {
            throw new NoTaskLeftException();
        }
        /** @var TaskModel|null $task */
        $task = $this->event->getTasks()->where('label', $taskLabel)->fetch();
        if (!$task) {
            throw new TaskCodeException(\sprintf(_('Task %s does not exist.'), $taskLabel));
        }
        return $task;
    }

    private function extractTeamId(string $code): int
    {
        $fullCode = self::createFullCode($code);
        return (int)substr($fullCode, 0, 6);
    }

    private function extractTaskLabel(string $code): string
    {
        $fullCode = self::createFullCode($code);
        return (string)substr($fullCode, 6, 2);
    }

    /**
     * @throws TaskCodeException
     */
    private function createFullCode(string $code): string
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

        $sum = 3 * ((int)$subCode[0] + (int)$subCode[3] + (int)$subCode[6])
            + 7 * ((int)$subCode[1] + (int)$subCode[4] + (int)$subCode[7])
            + ((int)$subCode[2] + (int)$subCode[5] + (int)$subCode[8]);
        if ($sum % 10 !== 0) {
            throw new ControlMismatchException();
        }
        return $fullCode;
    }
}
