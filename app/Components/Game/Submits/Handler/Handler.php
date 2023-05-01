<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits\Handler;

use FKSDB\Components\Game\Submits\AlreadyRevokedSubmitException;
use FKSDB\Components\Game\Submits\ClosedSubmittingException;
use FKSDB\Components\Game\Submits\PointsMismatchException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitState;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use Fykosak\NetteORM\Exceptions\ModelException;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Security\User;
use Tracy\Debugger;

abstract class Handler
{
    protected const DEBUGGER_LOG_PRIORITY = 'ctyrboj-info';

    protected User $user;
    protected EventModel $event;
    protected SubmitService $submitService;
    public MemoryLogger $logger;

    public function __construct(EventModel $event, Container $container)
    {
        $this->event = $event;
        $container->callInjects($this);
        $this->logger = new MemoryLogger();
    }

    public function inject(SubmitService $submitService, User $user): void
    {
        $this->user = $user;
        $this->submitService = $submitService;
    }

    protected function checkRequirements(TeamModel2 $team, TaskModel $task): void
    {
        if (!$team->hasOpenSubmitting()) {
            throw new ClosedSubmittingException($team);
        }
    }

    public function create(
        TaskModel $task,
        TeamModel2 $team,
        int $points,
        string $newState = SubmitState::NOT_CHECKED
    ): void {
        $this->checkRequirements($team, $task);
        $submit = $this->submitService->storeModel([
            'points' => $points,
            'fyziklani_task_id' => $task->fyziklani_task_id,
            'fyziklani_team_id' => $team->fyziklani_team_id,
            'state' => $newState,
        ]);
        $this->logEvent($submit, 'created', \sprintf(' points %d', $points));

        $this->logger->log(
            new Message(
                \sprintf(
                    _('Points saved; %d points, team: "%s" (%d), task: %s'),
                    $points,
                    $team->name,
                    $team->fyziklani_team_id,
                    $task->label
                ),
                Message::LVL_SUCCESS
            )
        );
    }

    /**
     * @throws AlreadyRevokedSubmitException
     * @throws ClosedSubmittingException
     */
    final public function revoke(SubmitModel $submit): void
    {
        $this->checkRequirements($submit->fyziklani_team, $submit->fyziklani_task);
        $submit->canRevoke();
        $this->submitService->storeModel([
            'points' => null,
            'state' => SubmitState::NOT_CHECKED,
            'modified' => new \DateTimeImmutable(),
        ], $submit);
        $this->logEvent($submit, 'revoked');
        $this->logger->log(
            new Message(
                \sprintf(_('Submit %d has been revoked.'), $submit->fyziklani_submit_id),
                Message::LVL_SUCCESS
            )
        );
    }

    /**
     * @throws ClosedSubmittingException
     * @throws PointsMismatchException
     * @throws ModelException
     */
    public function check(SubmitModel $submit, int $points): void
    {
        $this->checkRequirements($submit->fyziklani_team, $submit->fyziklani_task);
        if ($submit->points != $points) {
            throw new PointsMismatchException();
        }
        $this->submitService->storeModel([
            'state' => SubmitState::CHECKED,
        ], $submit);
        $this->logEvent($submit, 'checked');

        $this->logger->log(
            new Message(
                \sprintf(
                    _('Scoring has been checked. %d points, team "%s" (%d), task %s.'),
                    $points,
                    $submit->fyziklani_team->name,
                    $submit->fyziklani_team->fyziklani_team_id,
                    $submit->fyziklani_task->label
                ),
                Message::LVL_SUCCESS
            )
        );
    }

    /**
     * @throws ClosedSubmittingException
     * @throws ModelException
     */
    public function edit(SubmitModel $submit, int $points): void
    {
        $this->checkRequirements($submit->fyziklani_team, $submit->fyziklani_task);
        $this->submitService->storeModel([
            'points' => $points,
            'state' => SubmitState::CHECKED,
            'modified' => new \DateTimeImmutable(),
        ], $submit);
        $this->logEvent($submit, 'edited', \sprintf(' points %d', $points));
        $this->logger->log(
            new Message(
                \sprintf(
                    _('Points edited. %d points, team: "%s" (%d), task: %s'),
                    $points,
                    $submit->fyziklani_team->name,
                    $submit->fyziklani_team->fyziklani_team_id,
                    $submit->fyziklani_task->label
                ),
                Message::LVL_SUCCESS
            )
        );
    }

    protected function logEvent(SubmitModel $submit, string $action, string $appendLog = null): void
    {
        Debugger::log(
            \sprintf(
                'Submit %d was %s by %s' . $appendLog,
                $submit->getPrimary(),
                $action,
                $this->user->getIdentity()->getId()
            ),
            $this->logPriority()
        );
    }

    abstract public function handle(TeamModel2 $team, TaskModel $task, ?int $points): void;

    abstract public function logPriority(): string;
}
