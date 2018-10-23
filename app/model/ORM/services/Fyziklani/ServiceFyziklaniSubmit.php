<?php

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceFyziklaniSubmit extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_FYZIKLANI_SUBMIT;
    protected $modelClassName = 'ModelFyziklaniSubmit';

    /**
     * @param $taskId integer
     * @param $teamId integer
     * @return ModelFyziklaniSubmit|null
     */
    public function findByTaskAndTeam($taskId, $teamId) {
        if (!$taskId || !$teamId) {
            return null;
        }
        /**
         * @var $result ModelFyziklaniSubmit
         */
        $result = $this->getTable()->where([
            'fyziklani_task_id' => $taskId,
            'e_fyziklani_team_id' => $teamId
        ])->fetch();
        return $result ?: null;
    }

    /**
     * Syntactic sugar.
     * @param $eventId integer
     * @return \Nette\Database\Table\Selection|null
     */
    public function findAll($eventId) {
        $result = $this->getTable();
        if ($eventId) {
            $result->where('e_fyziklani_team_id.event_id', $eventId);
        }
        return $result ?: null;
    }

    public function submitExist($taskId, $teamId) {
        if (is_null($this->findByTaskAndTeam($taskId, $teamId))) {
            return false;
        }
        if (is_null($this->findByTaskAndTeam($taskId, $teamId)->points)) {
            return false;
        }
        return true;
    }

    public function getSubmits($eventId, $lastUpdated = null) {
        $query = $this->getTable()->where('e_fyziklani_team.event_id', $eventId);
        $submits = [];
        if ($lastUpdated) {
            $query->where('modified >= ?', $lastUpdated);
        }
        /**
         * @var $submit ModelFyziklaniSubmit
         */
        foreach ($query as $submit) {
            $submits[$submit->fyziklani_submit_id] = $submit->__toArray();
        }
        return $submits;
    }
}
