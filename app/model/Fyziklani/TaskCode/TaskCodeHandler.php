<?php

namespace FKSDB\model\Fyziklani;

use FKSDB\ORM\ModelEvent;
use ORM\Models\Events\ModelFyziklaniTeam;
use ORM\Services\Events\ServiceFyziklaniTeam;

class TaskCodeHandler {

    /**
     * @var \ServiceFyziklaniSubmit
     */
    private $serviceFyziklaniSubmit;
    /**
     * @var \ServiceFyziklaniTask
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

    public function __construct(ServiceFyziklaniTeam $serviceFyziklaniTeam, \ServiceFyziklaniTask $serviceFyziklaniTask, \ServiceFyziklaniSubmit $serviceFyziklaniSubmit, ModelEvent $event) {
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniSubmit = $serviceFyziklaniSubmit;
        $this->event = $event;

    }

    /**
     * @param $values
     * @param $httpData
     * @return string
     * @throws TaskCodeException
     */
    public function saveTaskCode($values, $httpData) {
        try {
            $this->checkTaskCode($values->taskCode);
            $points = 0;
            foreach ($httpData as $key => $value) {
                if (preg_match('/points([0-9])/', $key, $match)) {
                    $points = +$match[1];
                }
            }
            $log = $this->savePoints($values->taskCode, $points);
            return $log;
        } catch (TaskCodeException $e) {
            throw $e;
        }
    }

    /**
     * @param string $fullCode
     * @param int $points
     * @return string
     * @throws TaskCodeException
     */
    public function preProcess(string $fullCode, int $points): string {
        $response = new \ReactResponse();
        $response->setAct('submit');

        try {
            $this->checkTaskCode($fullCode);
            return $this->savePoints($fullCode, $points);
        } catch (TaskCodeException $e) {
            throw  $e;
        }
    }

    /**
     * @param string $code
     * @param int $points
     * @return string
     * @throws \Exception
     */
    private function savePoints(string $code, int $points): string {
        $fullCode = self::createFullCode($code);
        $teamId = TaskCodePreprocessor::extractTeamId($fullCode);
        $taskLabel = TaskCodePreprocessor::extractTaskLabel($fullCode);
        $taskId = $this->serviceFyziklaniTask->taskLabelToTaskId($taskLabel, $this->event);

        if (is_null($submit = $this->serviceFyziklaniSubmit->findByTaskAndTeam($taskId, $teamId))) {
            $submit = $this->serviceFyziklaniSubmit->createNew([
                'points' => $points,
                'fyziklani_task_id' => $taskId,
                'e_fyziklani_team_id' => $teamId,
                /* ugly, force current timestamp in database
                 * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
                 */
                'created' => null
            ]);
        } else {
            $this->serviceFyziklaniSubmit->updateModel($submit, [
                'points' => $points,
                /* ugly, exclude previous value of `modified` from query
                 * so that `modified` is set automatically by DB
                 * see https://dev.mysql.com/doc/refman/5.5/en/timestamp-initialization.html
                 */
                'modified' => null
            ]);
            $this->serviceFyziklaniSubmit->save($submit);
        }
        /**
         * @var $team ModelFyziklaniTeam
         */
        $team = $this->serviceFyziklaniTeam->findByPrimary($teamId);

        $taskName = $this->serviceFyziklaniTask->findByLabel($taskLabel, $this->event)->name;

        try {
            $this->serviceFyziklaniSubmit->save($submit);
            return sprintf(_('Body byly uloženy. %d bodů, tým: "%s" (%d), úloha: %s "%s"'), $points, $team->name, $teamId, $taskLabel, $taskName);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param string $code
     * @return string
     * @throws TaskCodeException
     */
    public static function createFullCode(string $code): string {
        $l = strlen($code);
        if ($l > 9) {
            throw new TaskCodeException(_('Code is too long'));
        }

        return str_repeat('0', 9 - $l) . strtoupper($code);
    }

    /**
     * @param string $code
     * @return bool
     * @throws TaskCodeException
     */
    public function checkTaskCode(string $code): bool {
        $fullCode = self::createFullCode($code);
        /** skontroluje pratnosť kontrolu */
        if (!TaskCodePreprocessor::checkControlNumber($fullCode)) {
            throw new TaskCodeException(_('Chybně zadaný kód úlohy.'));
        }
        /* Existenica týmu */
        $teamId = TaskCodePreprocessor::extractTeamId($fullCode);

        if (!$this->serviceFyziklaniTeam->teamExist($teamId, $this->event)) {
            throw new TaskCodeException(\sprintf(_('Tým %s neexistuje.'), $teamId));
        }
        $teamRow = $this->serviceFyziklaniTeam->findByPrimary($teamId);
        $team = ModelFyziklaniTeam::createFromTableRow($teamRow);
        /* otvorenie submitu */
        if (!$team->hasOpenSubmit()) {
            throw new TaskCodeException(_('Bodování tohoto týmu je uzavřené.'));
        }
        /* správny label */
        $taskLabel = TaskCodePreprocessor::extractTaskLabel($fullCode);
        $taskId = $this->serviceFyziklaniTask->taskLabelToTaskId($taskLabel, $this->event);
        if (!$taskId) {
            throw new TaskCodeException(sprintf(_('Úloha %s neexistuje.'), $taskLabel));
        }
        /* Nezadal sa duplicitne toto nieje editácia */
        if ($this->serviceFyziklaniSubmit->submitExist($taskId, $teamId)) {
            throw new TaskCodeException(sprintf(_('Úloha %s už byla zadaná.'), $taskLabel));
        }
        return true;
    }
}
