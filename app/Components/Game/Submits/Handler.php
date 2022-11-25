<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitState;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use FKSDB\Models\ORM\Services\Fyziklani\TaskService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Security\User;
use Tracy\Debugger;

abstract class Handler
{
    protected const DEBUGGER_LOG_PRIORITY = 'ctyrboj-info';
    protected const LOG_FORMAT = 'Submit %d was %s by %s';

    protected User $user;
    protected EventModel $event;
    protected TaskCodePreprocessor $taskCodePreprocessor;
    protected SubmitService $submitService;

    public function __construct(
        EventModel $event,
        Container $container
    ) {
        $this->event = $event;
        $container->callInjects($this);
    }

    public function inject(
        TeamService2 $teamService,
        TaskService $taskService,
        SubmitService $submitService,
        User $user
    ): void {
        $this->user = $user;
        $this->submitService = $submitService;
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
    public function preProcess(Logger $logger, string $code, ?int $points): void
    {
        $this->checkTaskCode($code);
        $task = $this->taskCodePreprocessor->getTask($code);
        $team = $this->taskCodePreprocessor->getTeam($code);

        $this->savePoints($logger, $team, $task, $points);
    }

    abstract protected function savePoints(Logger $logger, TeamModel2 $team, TaskModel $task, ?int $points): void;

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

    abstract public function create(Logger $logger, TaskModel $task, TeamModel2 $team, ?int $points): void;

    /**
     * @throws AlreadyRevokedSubmitException
     * @throws ClosedSubmittingException
     */
    final public function revoke(Logger $logger, SubmitModel $submit): void
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

    abstract public function check(Logger $logger, SubmitModel $submit, ?int $points): void;

    abstract public function edit(Logger $logger, SubmitModel $submit, ?int $points): void;

    protected function logEvent(SubmitModel $submit, string $action, string $appendLog = null): void
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
