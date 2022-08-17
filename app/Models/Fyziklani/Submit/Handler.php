<?php

declare(strict_types=1);

namespace FKSDB\Models\Fyziklani\Submit;

use FKSDB\Models\ORM\Models\Fyziklani\SubmitState;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use FKSDB\Models\ORM\Services\Fyziklani\TaskService;
use Nette\DI\Container;
use Nette\Security\User;
use Tracy\Debugger;

class Handler
{
    public const DEBUGGER_LOG_PRIORITY = 'fyziklani-info';
    public const LOG_FORMAT = 'Submit %d was %s by %s';
    private SubmitService $submitService;
    private User $user;
    private EventModel $event;
    private TaskCodePreprocessor $taskCodePreprocessor;

    public function __construct(
        EventModel $event,
        Container $container
    ) {
        $this->event = $event;
        $container->callInjects($this);
    }

    public function injectPrimary(
        TeamService2 $teamService,
        TaskService $taskService,
        SubmitService $submitService,
        User $user
    ): void {
        $this->submitService = $submitService;
        $this->user = $user;
        $this->taskCodePreprocessor = new TaskCodePreprocessor(
            $this->event,
            $teamService,
            $taskService
        );
    }

    /**
     * @throws PointsMismatchException
     * @throws TaskCodeException
     * @throws ClosedSubmittingException
     */
    public function preProcess(Logger $logger, string $code, int $points): void
    {
        $this->checkTaskCode($code);
        $this->savePoints($logger, $code, $points);
    }

    /**
     * @throws PointsMismatchException
     * @throws TaskCodeException
     * @throws ClosedSubmittingException
     */
    private function savePoints(Logger $logger, string $code, int $points): void
    {
        $task = $this->taskCodePreprocessor->getTask($code);
        $team = $this->taskCodePreprocessor->getTeam($code);

        $submit = $this->submitService->findByTaskAndTeam($task, $team);
        if (is_null($submit)) { // novo zadaný
            $this->createSubmit($logger, $task, $team, $points);
        } elseif (is_null($submit->points)) { // ak bol zmazaný
            $this->changePoints($logger, $submit, $points);
        } elseif (!$submit->isChecked()) { // check bodovania
            $this->checkSubmit($logger, $submit, $points);
        } else {
            throw new TaskCodeException(_('Task given and validated.'));
        }
    }

    /**
     * @throws TaskCodeException
     * @throws ClosedSubmittingException
     */
    private function checkTaskCode(string $code): void
    {
        $fullCode = $this->taskCodePreprocessor->createFullCode($code);
        /* skontroluje pratnosť kontrolu */
        if (!$this->taskCodePreprocessor->checkControlNumber($fullCode)) {
            throw new ControlMismatchException();
        }
        $team = $this->taskCodePreprocessor->getTeam($code);
        /* otvorenie submitu */
        if (!$team->hasOpenSubmitting()) {
            throw new ClosedSubmittingException($team);
        }
        $this->taskCodePreprocessor->getTask($code);
    }

    /**
     * @throws ClosedSubmittingException
     * @throws ModelException
     */
    public function changePoints(Logger $logger, SubmitModel $submit, int $points): void
    {
        if (!$submit->fyziklani_team->hasOpenSubmitting()) {
            throw new ClosedSubmittingException($submit->fyziklani_team);
        }
        $this->submitService->storeModel([
            'points' => $points,
            'state' => SubmitState::CHECKED,
            'modified' => new \DateTimeImmutable(),
        ], $submit);
        $this->logEvent($submit, 'edited', \sprintf(' points %d', $points));
        $logger->log(
            new Message(
                \sprintf(
                    _('Points edited. %d points, team: "%s" (%d), task: %s "%s"'),
                    $points,
                    $submit->fyziklani_team->name,
                    $submit->fyziklani_team->fyziklani_team_id,
                    $submit->fyziklani_task->label,
                    $submit->fyziklani_task->name
                ),
                Message::LVL_SUCCESS
            )
        );
    }

    /**
     * @throws AlreadyRevokedSubmitException
     * @throws ClosedSubmittingException
     * @throws ModelException
     */
    public function revokeSubmit(Logger $logger, SubmitModel $submit): void
    {
        if ($submit->canRevoke(true)) {
            $this->submitService->storeModel([
                'points' => null,
                'state' => SubmitState::NOT_CHECKED,
                'modified' => new \DateTimeImmutable(),
            ], $submit);
            $this->logEvent($submit, 'revoked');
            $logger->log(
                new Message(
                    \sprintf(_('Submit %d has been revoked.'), $submit->fyziklani_submit_id),
                    Message::LVL_SUCCESS
                )
            );
        }
    }

    /**
     * @throws ClosedSubmittingException
     * @throws PointsMismatchException
     * @throws ModelException
     */
    public function checkSubmit(Logger $logger, SubmitModel $submit, int $points): void
    {
        if (!$submit->fyziklani_team->hasOpenSubmitting()) {
            throw new ClosedSubmittingException($submit->fyziklani_team);
        }
        if ($submit->points != $points) {
            throw new PointsMismatchException();
        }
        $this->submitService->storeModel([
            'state' => SubmitState::CHECKED,
        ], $submit);
        $this->logEvent($submit, 'checked');

        $logger->log(
            new Message(
                \sprintf(
                    _('Scoring has been checked. %d points, team "%s" (%d), task %s "%s".'),
                    $points,
                    $submit->fyziklani_team->name,
                    $submit->fyziklani_team->fyziklani_team_id,
                    $submit->fyziklani_task->label,
                    $submit->fyziklani_task->name
                ),
                Message::LVL_SUCCESS
            )
        );
    }

    public function createSubmit(Logger $logger, TaskModel $task, TeamModel2 $team, int $points): void
    {
        $submit = $this->submitService->storeModel([
            'points' => $points,
            'fyziklani_task_id' => $task->fyziklani_task_id,
            'fyziklani_team_id' => $team->fyziklani_team_id,
            'state' => SubmitState::NOT_CHECKED,
        ]);
        $this->logEvent($submit, 'created', \sprintf(' points %d', $points));

        $logger->log(
            new Message(
                \sprintf(
                    _('Points saved; %d points, team: "%s" (%d), task: %s "%s"'),
                    $points,
                    $team->name,
                    $team->fyziklani_team_id,
                    $task->label,
                    $task->name
                ),
                Message::LVL_SUCCESS
            )
        );
    }

    private function logEvent(SubmitModel $submit, string $action, string $appendLog = null): void
    {
        Debugger::log(
            \sprintf(
                self::LOG_FORMAT . $appendLog,
                $submit->getPrimary(),
                $action,
                $this->user->getIdentity()->getId()
            ),
            self::DEBUGGER_LOG_PRIORITY
        );
    }
}
