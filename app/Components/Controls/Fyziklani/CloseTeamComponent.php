<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\Fyziklani\NotSetGameParametersException;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Nette\Database\Connection;
use Nette\DI\Container;

class CloseTeamComponent extends BaseComponent {

    private ModelFyziklaniTeam $team;
    private Connection $connection;

    public function __construct(Container $container, ModelFyziklaniTeam $team) {
        parent::__construct($container);
        $this->team = $team;
    }

    final public function injectServiceFyziklaniTask(Connection $connection): void {
        $this->connection = $connection;
    }

    public function handleClose(): void {
        $this->connection->beginTransaction();
        $sum = (int)$this->team->getNonRevokedSubmits()->sum('points');
        $this->team->update([
            'points' => $sum,
        ]);
        $this->connection->commit();
        $this->getPresenter()->flashMessage(\sprintf(_('Team "%s" has successfully closed submitting, with total %d points.'), $this->team->name, $sum), BasePresenter::FLASH_SUCCESS);
        $this->getPresenter()->redirect('list', ['id' => null]);
    }

    /**
     * @throws NotSetGameParametersException
     */
    final public function render(): void {
        $this->template->task = $this->getNextTask();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.closeTeam.latte');
    }

    /**
     * @throws NotSetGameParametersException
     */
    private function getNextTask(): string {
        $submits = count($this->team->getNonRevokedSubmits());
        $tasksOnBoard = $this->team->getEvent()->getFyziklaniGameSetup()->tasks_on_board;
        /** @var ModelFyziklaniTask|null $nextTask */
        $nextTask = $this->team->getEvent()->getFyziklaniTasks()->order('label')->limit(1, $submits + $tasksOnBoard)->fetch();
        return ($nextTask) ? $nextTask->label : '';
    }
}
