<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\Fyziklani\NotSetGameParametersException;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use Nette\Application\AbortException;
use Nette\DI\Container;

/**
 * Class CloseTeamControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class CloseTeamControl extends BaseComponent {

    private ModelEvent $event;

    /** @var ModelFyziklaniTeam */
    private $team;

    private ServiceFyziklaniTask $serviceFyziklaniTask;

    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container);
        $this->event = $event;
    }

    final public function injectServiceFyziklaniTask(ServiceFyziklaniTask $serviceFyziklaniTask): void {
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
    }

    public function setTeam(ModelFyziklaniTeam $team): void {
        $this->team = $team;
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function handleClose(): void {
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
    public function render(): void {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.closeTeam.latte');
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
