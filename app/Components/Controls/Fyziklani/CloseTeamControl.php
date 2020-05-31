<?php

namespace FKSDB\Components\Controls\Fyziklani;

use BasePresenter;
use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use Nette\Application\AbortException;
use Nette\DI\Container;

class CloseTeamControl extends BaseComponent {
    /**
     * @var ModelEvent
     */
    private $event;
    /**
     * @var ModelFyziklaniTeam
     */
    private $team;
    /**
     * @var ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;

    /**
     * CloseTeamControl constructor.
     * @param Container $container
     * @param ModelEvent $event
     */
    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container);
        $this->event = $event;
    }

    public function injectServiceFyziklaniTask(ServiceFyziklaniTask $serviceFyziklaniTask): void {
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
    }

    /**
     * @param ModelFyziklaniTeam $team
     * @return void
     */
    public function setTeam(ModelFyziklaniTeam $team) {
        $this->team = $team;
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function handleClose() {
        $connection = $this->serviceFyziklaniTask->getConnection();
        $connection->beginTransaction();
        $sum = (int)$this->team->getNonRevokedSubmits()->sum('points');
        $this->team->update([
            'points' => $sum,
        ]);
        $connection->commit();
        $this->getPresenter()->flashMessage(\sprintf(_('Team "%s" has successfully closed submitting, with total %d points.'), $this->team->name, $sum), BasePresenter::FLASH_SUCCESS);
        $this->getPresenter()->redirect('list', ['id' => null]);
    }

    /**
     * @return void
     * @throws NotSetGameParametersException
     */
    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'CloseTeamControl.latte');
        $this->template->task = $this->getNextTask();
        $this->template->render();
    }

    /**
     * @return string
     * @throws NotSetGameParametersException
     */
    private function getNextTask(): string {
        $submits = count($this->team->getNonRevokedSubmits());
        $tasksOnBoard = $this->event->getFyziklaniGameSetup()->tasks_on_board;
        /** @var ModelFyziklaniTask|null $nextTask */
        $nextTask = $this->serviceFyziklaniTask->findAll($this->event)->order('label')->limit(1, $submits + $tasksOnBoard)->fetch();
        return ($nextTask) ? $nextTask->label : '';
    }
}
