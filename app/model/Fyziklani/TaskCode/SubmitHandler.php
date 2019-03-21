<?php

namespace FKSDB\model\Fyziklani;

use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniSubmit;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;

/**
 * Class TaskCodeHandler
 * @package FKSDB\model\Fyziklani
 */
class SubmitHandler {

    /**
     * @var ServiceFyziklaniSubmit
     */
    private $serviceFyziklaniSubmit;
    /**
     * @var ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;
    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * TaskCodeHandler constructor.
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @param ServiceFyziklaniTask $serviceFyziklaniTask
     * @param ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     * @param ModelEvent $event
     */
    public function __construct(ServiceFyziklaniTeam $serviceFyziklaniTeam, ServiceFyziklaniTask $serviceFyziklaniTask, ServiceFyziklaniSubmit $serviceFyziklaniSubmit, ModelEvent $event) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->event = $event;
    }

    /**
     * @param string $code
     * @param int $points
     * @return string
     * @throws ClosedSubmittingException
     * @throws TaskCodeException
     * @throws \Exception
     */
    public function preProcess(string $code, int $points): string {
        $this->checkTaskCode($code);
        return $this->savePoints($code, $points);
    }

    /**
     * @param ModelFyziklaniTask $task
     * @param ModelFyziklaniTeam $team
     * @param int $points
     * @return string
     */
    private function createSubmit(ModelFyziklaniTask $task, ModelFyziklaniTeam $team, int $points): string {
        /**
         * @var ModelFyziklaniSubmit $submit
         */
        $submit = $this->serviceFyziklaniSubmit->createNew([
            'points' => $points,
            'fyziklani_task_id' => $task->fyziklani_task_id,
            'e_fyziklani_team_id' => $team->e_fyziklani_team_id,
            /* ugly, force current timestamp in database
             * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
             */
            'state' => ModelFyziklaniSubmit::STATE_NOT_CHECKED,
            'created' => null
        ]);
        $this->serviceFyziklaniSubmit->save($submit);
        return \sprintf(_('Body byly uloženy. %d bodů, tým: "%s" (%d), úloha: %s "%s"'),
            $points,
            $team->name,
            $team->e_fyziklani_team_id,
            $task->label,
            $task->name);
    }

    /**
     * @param string $code
     * @param int $points
     * @return string
     * @throws ClosedSubmittingException
     * @throws PointsMismatchException
     * @throws TaskCodeException
     */
    private function savePoints(string $code, int $points): string {
        $task = $this->getTask($code);
        $team = $this->getTeam($code);

        $submit = $this->serviceFyziklaniSubmit->findByTaskAndTeam($task, $team);
        if (is_null($submit)) { // novo zadaný
            return $this->createSubmit($task, $team, $points);
        } elseif (!$submit->isChecked()) { // check bodovania
            return $submit->check($points);
        } elseif (!$submit->points) { // ak bol zmazaný
            return $submit->changePoints($points);
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
        // stupid touch to label
        $this->getTask($code);
        return true;
    }

    /**
     * @param string $code
     * @return ModelFyziklaniTeam
     * @throws TaskCodeException
     */
    public function getTeam(string $code): ModelFyziklaniTeam {
        $fullCode = TaskCodePreprocessor::createFullCode($code);

        $teamId = TaskCodePreprocessor::extractTeamId($fullCode);

        if (!$this->serviceFyziklaniTeam->teamExist($teamId, $this->event)) {
            throw new TaskCodeException(\sprintf(_('Tým %s neexistuje.'), $teamId));
        }
        $teamRow = $this->serviceFyziklaniTeam->findByPrimary($teamId);
        return ModelFyziklaniTeam::createFromTableRow($teamRow);
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
            throw new TaskCodeException(sprintf(_('Úloha %s neexistuje.'), $taskLabel));
        }

        return $task;
    }
}
