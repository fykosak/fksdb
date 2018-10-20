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

    public function saveTaskCode($values, $httpData) {

        if ($this->checkTaskCode($values->taskCode, $msg)) {
            $points = 0;
            foreach ($httpData as $key => $value) {
                if (preg_match('/points([0-9])/', $key, $match)) {
                    $points = +$match[1];
                }
            }
            $log = $this->savePoints($values->taskCode, $points);
            return $log;
        } else {
            $this->flashMessage($msg, 'danger');
            // $this->redirect('this');
        }
    }

    private function savePoints($fullCode, $points) {
        $teamId = TaskCodePreprocessor::extractTeamId($fullCode);
        $taskLabel = TaskCodePreprocessor::extractTaskLabel($fullCode);
        $taskId = $this->serviceFyziklaniTask->taskLabelToTaskId($taskLabel, $this->event->event_id);

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

        $taskName = $this->serviceFyziklaniTask->findByLabel($taskLabel, $this->event->event_id)->name;

        try {
            $this->serviceFyziklaniSubmit->save($submit);

            return [sprintf(_('Body byly uloženy. %d bodů, tým: "%s" (%d), úloha: %s "%s"'), $points, $team->name, $teamId, $taskLabel, $taskName), 'success'];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private function checkTaskCode($taskCode, &$msg) {
        /** skontroluje pratnosť kontrolu */
        if (!TaskCodePreprocessor::checkControlNumber($taskCode)) {
            $msg = _('Chybně zadaný kód úlohy.');
            return false;
        }
        /* Existenica týmu */
        $teamId = TaskCodePreprocessor::extractTeamId($taskCode);


        if (!$this->serviceFyziklaniTeam->teamExist($teamId, $this->event->event_id)) {
            $msg = sprintf(_('Tým %s neexistuje.'), $teamId);
            return false;
        }
        /**
         * @var $team ModelFyziklaniTeam
         */
        $team = $this->serviceFyziklaniTeam->findByPrimary($teamId);
        /* otvorenie submitu */
        if (!$team->hasOpenSubmit()) {
            $msg = _('Bodování tohoto týmu je uzavřené.');
            return false;
        }
        /* správny label */
        $taskLabel = TaskCodePreprocessor::extractTaskLabel($taskCode);
        $taskId = $this->serviceFyziklaniTask->taskLabelToTaskId($taskLabel, $this->event->event_id);
        if (!$taskId) {
            $msg = sprintf(_('Úloha %s neexistuje.'), $taskLabel);
            return false;
        }
        /* Nezadal sa duplicitne toto nieje editácia */
        if ($this->serviceFyziklaniSubmit->submitExist($taskId, $teamId)) {
            $msg = sprintf(_('Úloha %s už byla zadaná.'), $taskLabel);
            return false;
        }
        return true;
    }
}
