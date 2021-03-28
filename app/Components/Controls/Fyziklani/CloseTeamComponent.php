<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\Fyziklani\NotSetGameParametersException;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use Nette\DI\Container;

/**
 * Class CloseTeamControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class CloseTeamComponent extends BaseComponent {

    private ModelFyziklaniTeam $team;
    private ServiceFyziklaniTask $serviceFyziklaniTask;

    public function __construct(Container $container, ModelFyziklaniTeam $team) {
        parent::__construct($container);
        $this->team = $team;
    }

    final public function injectServiceFyziklaniTask(ServiceFyziklaniTask $serviceFyziklaniTask): void {
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
    }

    public function handleClose(): void {
        $connection = $this->serviceFyziklaniTask->explorer->getConnection();
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
    final public function render(): void {
        $this->template->task = $this->getNextTask();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.closeTeam.latte');
    }

    /**
     * @return string
     * @throws NotSetGameParametersException
     */
    private function getNextTask(): string {
        $submits = count($this->team->getNonRevokedSubmits());
        $tasksOnBoard = $this->team->getEvent()->getFyziklaniGameSetup()->tasks_on_board;
        /** @var ModelFyziklaniTask|null $nextTask */
        $nextTask = $this->serviceFyziklaniTask->findAll($this->team->getEvent())->order('label')->limit(1, $submits + $tasksOnBoard)->fetch();
        return ($nextTask) ? $nextTask->label : '';
    }
}
