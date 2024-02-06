<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits\Handler;

use FKSDB\Components\Game\Submits\AlreadyRevokedSubmitException;
use FKSDB\Components\Game\Submits\ClosedSubmittingException;
use FKSDB\Components\Game\Submits\PointsMismatchException;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitState;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Services\Fyziklani\SubmitService;
use Fykosak\Utils\Logging\MemoryLogger;
use Fykosak\Utils\Logging\Message;
use Nette\Application\LinkGenerator;
use Nette\DI\Container;
use Nette\Security\User;
use Nette\Utils\Html;
use Tracy\Debugger;

abstract class Handler
{
    protected User $user;
    protected SubmitService $submitService;
    public MemoryLogger $logger;
	protected LinkGenerator $linkGenerator; 

    public function __construct(Container $container)
    {
        $container->callInjects($this);
        $this->logger = new MemoryLogger();
    }

    public function inject(SubmitService $submitService, User $user, LinkGenerator $linkGenerator): void
    {
        $this->user = $user;
        $this->submitService = $submitService;
        $this->linkGenerator = $linkGenerator;
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
        SubmitState $newState
    ): void {
        $this->checkRequirements($team, $task);
        $submit = $this->submitService->create($task, $team, $points, $newState);

        $this->logEvent($submit, 'created', \sprintf(' points %d', $points));

        $this->logger->log(
            new Message(
                \sprintf(
                    _('Points saved; points: %d, team: "%s" (%d), task: %s, edit: %s.'),
                    $points,
                    $team->name,
                    $team->fyziklani_team_id,
                    $task->label,
                    $this->getTaskEditLink($submit)
                ),
                $newState->value === SubmitState::NotChecked ? Message::LVL_INFO : Message::LVL_SUCCESS
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
        $this->submitService->revoke($submit);
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
     * @throws \PDOException
     */
    public function check(SubmitModel $submit, int $points): void
    {
        $this->checkRequirements($submit->fyziklani_team, $submit->fyziklani_task);
        if ($submit->points != $points) {
            throw new PointsMismatchException();
        }
        $this->submitService->check($submit, $points);
        $this->logEvent($submit, 'checked');

        $this->logger->log(
            new Message(
                \sprintf(
                    _('Scoring has been checked; points: %d, team "%s" (%d), task %s, edit: %s.'),
                    $points,
                    $submit->fyziklani_team->name,
                    $submit->fyziklani_team->fyziklani_team_id,
                    $submit->fyziklani_task->label,
                    $this->getTaskEditLink($submit)
                ),
                Message::LVL_SUCCESS
            )
        );
    }

    /**
     * @throws ClosedSubmittingException
     * @throws \PDOException
     */
    public function edit(SubmitModel $submit, int $points): void
    {
        $this->checkRequirements($submit->fyziklani_team, $submit->fyziklani_task);
        $this->submitService->edit($submit, $points);
        $this->logEvent($submit, 'edited', \sprintf(' points %d', $points));
        $this->logger->log(
            new Message(
                \sprintf(
                    _('Points edited; points: %d, team: "%s" (%d), task: %s, edit: %s.'),
                    $points,
                    $submit->fyziklani_team->name,
                    $submit->fyziklani_team->fyziklani_team_id,
                    $submit->fyziklani_task->label,
                    $this->getTaskEditLink($submit)
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

    protected function getTaskEditLink(SubmitModel $submit): string
    {
        $link = $this->linkGenerator->link('Game:Submit:edit', ['eventId' => $submit->fyziklani_task->event_id, 'id' => $submit->fyziklani_submit_id]);
        return sprintf('<a href="%s" target="_blank">%s</a>', $link, $link);
    }

    abstract public function handle(TeamModel2 $team, TaskModel $task, ?int $points): void;

    abstract public function logPriority(): string;
}
