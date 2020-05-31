<?php

namespace FKSDB\Fyziklani;

use FKSDB\Messages\Message;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Database\Table\ActiveRow;
use Nette\DI\Container;
use Nette\Security\User;

/**
 * Class SubmitHandler
 * @author Michal Červeňák <miso@fykos.cz>
 */
class SubmitHandler {

    private ServiceFyziklaniSubmit $serviceFyziklaniSubmit;

    private ServiceFyziklaniTask $serviceFyziklaniTask;

    private ServiceFyziklaniTeam $serviceFyziklaniTeam;

    private ModelEvent $event;

    /**
     * TaskCodeHandler constructor.
     * @param Container $container
     * @param ModelEvent $event
     */
    public function __construct(Container $container, ModelEvent $event) {
        $this->serviceFyziklaniTeam = $container->getByType(ServiceFyziklaniTeam::class);
        $this->serviceFyziklaniTask = $container->getByType(ServiceFyziklaniTask::class);
        $this->serviceFyziklaniSubmit = $container->getByType(ServiceFyziklaniSubmit::class);
        $this->event = $event;
    }

    /**
     * @param string $code
     * @param int $points
     * @param User $user
     * @return Message
     * @throws ClosedSubmittingException
     * @throws PointsMismatchException
     * @throws TaskCodeException
     */
    public function preProcess(string $code, int $points, User $user): Message {
        $this->checkTaskCode($code);
        return $this->savePoints($code, $points, $user);
    }

    /**
     * @param string $code
     * @param int $points
     * @param User $user
     * @return Message
     * @throws ClosedSubmittingException
     * @throws PointsMismatchException
     * @throws TaskCodeException
     */
    private function savePoints(string $code, int $points, User $user): Message {
        $task = $this->getTask($code);
        $team = $this->getTeam($code);

        $submit = $this->serviceFyziklaniSubmit->findByTaskAndTeam($task, $team);
        if (is_null($submit)) { // novo zadaný
            return $this->serviceFyziklaniSubmit->createSubmit($task, $team, $points, $user);
        } elseif (!$submit->isChecked()) { // check bodovania
            return $this->serviceFyziklaniSubmit->checkSubmit($submit, $points, $user);
        } elseif (is_null($submit->points)) { // ak bol zmazaný
            return $this->serviceFyziklaniSubmit->changePoints($submit, $points, $user);
        } else {
            throw new TaskCodeException(\sprintf(_('Úloha je zadaná a overená.')));
        }
    }

    /**
     * @param string $code
     * @return bool
     * @throws ClosedSubmittingException
     * @throws TaskCodeException
     */
    public function checkTaskCode(string $code): bool {
        $fullCode = TaskCodePreprocessor::createFullCode($code);
        /* skontroluje pratnosť kontrolu */
        if (!TaskCodePreprocessor::checkControlNumber($fullCode)) {
            throw new ControlMismatchException();
        }
        $team = $this->getTeam($code);
        /* otvorenie submitu */
        if (!$team->hasOpenSubmitting()) {
            throw new ClosedSubmittingException($team);
        }
        $this->getTask($code);
        return true;
    }

    /**
     * @param string $code
     * @return ModelFyziklaniTeam|ActiveRow
     * @throws TaskCodeException
     */
    public function getTeam(string $code): ModelFyziklaniTeam {
        $fullCode = TaskCodePreprocessor::createFullCode($code);

        $teamId = TaskCodePreprocessor::extractTeamId($fullCode);

        if (!$this->serviceFyziklaniTeam->teamExist($teamId, $this->event)) {
            throw new TaskCodeException(\sprintf(_('Tým %s neexistuje.'), $teamId));
        }
        return $this->serviceFyziklaniTeam->findByPrimary($teamId);
    }

    /**
     * @param string $code
     * @return ModelFyziklaniTask
     * @throws TaskCodeException
     */
    public function getTask(string $code): ModelFyziklaniTask {
        $fullCode = TaskCodePreprocessor::createFullCode($code);
        /* správny label */
        $taskLabel = TaskCodePreprocessor::extractTaskLabel($fullCode);
        $task = $this->serviceFyziklaniTask->findByLabel($taskLabel, $this->event);
        if (!$task) {
            throw new TaskCodeException(\sprintf(_('Úloha %s neexistuje.'), $taskLabel));
        }

        return $task;
    }
}
