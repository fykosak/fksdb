<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use Fykosak\Utils\BaseComponent\BaseComponent;
use FKSDB\Models\Fyziklani\NotSetGameParametersException;
use FKSDB\Models\ORM\Models\Fyziklani\TaskModel;
use Fykosak\Utils\Logging\Message;
use Nette\Database\Connection;
use Nette\DI\Container;

class CloseTeamComponent extends BaseComponent
{

    private TeamModel2 $team;
    private Connection $connection;

    public function __construct(Container $container, TeamModel2 $team)
    {
        parent::__construct($container);
        $this->team = $team;
    }

    final public function injectServiceFyziklaniTask(Connection $connection): void
    {
        $this->connection = $connection;
    }

    public function handleClose(): void
    {
        $this->connection->beginTransaction();
        $sum = (int)$this->team->getNonRevokedSubmits()->sum('points');
        $this->team->update([
            'points' => $sum,
        ]);
        $this->connection->commit();
        $this->getPresenter()->flashMessage(
            \sprintf(_('Team "%s" has successfully closed submitting, with total %d points.'), $this->team->name, $sum),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('list', ['id' => null]);
    }

    /**
     * @throws NotSetGameParametersException
     */
    final public function render(): void
    {
        $this->template->task = $this->getNextTask();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.closeTeam.latte');
    }

    /**
     * @throws NotSetGameParametersException
     */
    private function getNextTask(): string
    {
        $submits = $this->team->getNonRevokedSubmits()->count('*');
        $tasksOnBoard = $this->team->event->getFyziklaniGameSetup()->tasks_on_board;
        /** @var TaskModel|null $nextTask */
        $nextTask = $this->team->event
            ->getFyziklaniTasks()
            ->order('label')
            ->limit(1, $submits + $tasksOnBoard)
            ->fetch();
        return ($nextTask) ? $nextTask->label : '';
    }
}
