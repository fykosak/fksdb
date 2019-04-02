<?php

namespace FKSDB\model\Fyziklani;

use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Tracy\Debugger;

/**
 * Class TaskCodeHandler
 * @package FKSDB\model\Fyziklani
 */
class TaskCodeHandler {

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
     * @var \FKSDB\ORM\Models\ModelEvent
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
        try {
            $this->checkTaskCode($code);
            return $this->savePoints($code, $points);
        } catch (TaskCodeException $exception) {
            throw $exception;
        }
    }

    /**
     * @param string $code
     * @param int $points
     * @return string
     * @throws \Exception
     */
    private function savePoints(string $code, int $points): string {
        $fullCode = TaskCodePreprocessor::createFullCode($code);
        $teamId = TaskCodePreprocessor::extractTeamId($fullCode);
        $taskLabel = TaskCodePreprocessor::extractTaskLabel($fullCode);
        $task = $this->serviceFyziklaniTask->findByLabel($taskLabel, $this->event);

        if (is_null($submit = $this->serviceFyziklaniSubmit->findByTaskAndTeam($task->fyziklani_task_id, $teamId))) {
            $submit = $this->serviceFyziklaniSubmit->createNewModel([
                'points' => $points,
                'fyziklani_task_id' => $task->fyziklani_task_id,
                'e_fyziklani_team_id' => $teamId,
                /* ugly, force current timestamp in database
                 * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
                 */
                'created' => null
            ]);
        } else {
            $submit->update([
                'points' => $points,
                /* ugly, exclude previous value of `modified` from query
                 * so that `modified` is set automatically by DB
                 * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
                 */
                'modified' => null
            ]);
        }

        $team = $submit->getTeam();

        $taskName = $this->serviceFyziklaniTask->findByLabel($taskLabel, $this->event)->name;

        try {
            $this->serviceFyziklaniSubmit->save($submit);
            return sprintf(_('Body byly uloženy. %d bodů, tým: "%s" (%d), úloha: %s "%s"'), $points, $team->name, $teamId, $taskLabel, $taskName);
        } catch (\Exception $exception) {
            throw $exception;
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
            throw new TaskCodeException(_('Chybně zadaný kód úlohy.'));
        }
        $team = $this->getTeamFromCode($code);
        /* otvorenie submitu */
        if (!$team->hasOpenSubmitting()) {
            throw new ClosedSubmittingException($team);
        }
        $task = $this->getTaskFromCode($code);
        /* Nezadal sa duplicitne toto nieje editácia */
        Debugger::barDump($task);
        if ($this->serviceFyziklaniSubmit->submitExist($task->fyziklani_task_id, $team->e_fyziklani_team_id)) {
            throw new TaskCodeException(sprintf(_('Úloha %s už byla zadaná.'), $task->label));
        }
        return true;
    }

    /**
     * @param string $code
     * @return \FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam
     * @throws TaskCodeException
     */
    public function getTeamFromCode(string $code): ModelFyziklaniTeam {
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
    public function getTaskFromCode(string $code): ModelFyziklaniTask {
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
