<?php

declare(strict_types=1);

namespace FKSDB\Models\Fyziklani\Submit;

use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\Fyziklani\TaskService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;

final class TaskCodePreprocessor
{
    private TaskService $taskService;
    private TeamService2 $teamService;
    private EventModel $event;

    public function __construct(
        EventModel $event,
        TeamService2 $teamService,
        TaskService $taskService
    ) {
        $this->teamService = $teamService;
        $this->taskService = $taskService;
        $this->event = $event;
    }

    public static function checkControlNumber(string $code): bool
    {
        if (strlen($code) != 9) {
            return false;
        }
        $subCode = str_split(self::getNumLabel($code));

        // Hotfix for Fyziklani2022
        // Decrement task number by 1 and handle special case for 11 => 18
        $subCode[7]--;
        if ($subCode[7] == 0) {
            $subCode[6]--;
            if ($subCode[6] == 0) {
                $subCode[6] = 1;
            }
            $subCode[7] = 8;
        }
        // End of hotfix for Fyziklani2022

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
    public function getTeam(string $code): TeamModel2
    {
        $fullCode = self::createFullCode($code);

        $teamId = self::extractTeamId($fullCode);
        $team = $this->teamService->findByPrimary($teamId);
        if (!$team || ($team->event_id !== $this->event->event_id)) {
            throw new TaskCodeException(\sprintf(_('Team %s does not exists.'), $teamId));
        }
        return $team;
    }

    /**
     * @throws TaskCodeException
     */
    public function getTask(string $code): TaskModel
    {
        $fullCode = self::createFullCode($code);
        /* correct label */
        $taskLabel = self::extractTaskLabel($fullCode);
        $task = $this->taskService->findByLabel($taskLabel, $this->event);
        if (!$task) {
            throw new TaskCodeException(\sprintf(_('Task %s does not exists.'), $taskLabel));
        }
        return $task;
    }
}
