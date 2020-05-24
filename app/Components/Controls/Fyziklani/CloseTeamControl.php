<?php

namespace FKSDB\Components\Controls\Fyziklani;

use BasePresenter;
use FKSDB\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\DI\Container;
use Nette\Localization\ITranslator;

/**
 * Class CloseTeamControl
 * @package FKSDB\Components\Controls\Fyziklani
 */
class CloseTeamControl extends Control {
    /**
     * @var ModelEvent
     */
    private $event;
    /**
     * @var ITranslator
     */
    private $translator;
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
    public function __construct(
        Container $container,
        ModelEvent $event
    ) {
        parent::__construct();
        $this->event = $event;
        $this->translator = $container->getByType(ITranslator::class);
        $this->serviceFyziklaniTask = $container->getByType(ServiceFyziklaniTask::class);
    }

    /**
     * @param ModelFyziklaniTeam $team
     */
    public function setTeam(ModelFyziklaniTeam $team) {
        $this->team = $team;
    }

    /**
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
     * @throws NotSetGameParametersException
     */
    public function render() {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'CloseTeamControl.latte');
        $this->template->setTranslator($this->translator);
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
