<?php

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceFyziklaniSubmit extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_FYZIKLANI_SUBMIT;
    protected $modelClassName = 'ModelFyziklaniSubmit';
    
    /**
     * Syntactic sugar.
     * 
     * @return ModelFyziklaniSubmit|null
     */
    public function findByTaskAndTeam($taskId, $teamId) {
        if (!$taskId || !$teamId) {
            return null;
        }
        $result = $this->getTable()->where(array(
            'fyziklani_task_id' => $taskId, 
            'e_fyziklani_team_id' => $teamId
        ))->fetch();
        return $result ? : null;
    }
    
    /**
     * Syntactic sugar.
     * 
     * @return \Nette\Database\Table\Selection|null
     */
    public function findAll($eventId) {
        $result = $this->getTable();
        if ($eventId) {
            $result->where('e_fyziklani_team_id.event_id', $eventId);
        }        
        return $result ? : null;
    }

    public function submitExist($taskID, $teamID) {
        if(is_null($this->findByTaskAndTeam($taskID, $teamID))){
            return false;
        }
        if(is_null($this->findByTaskAndTeam($taskID, $teamID)->points)){
            return false;
        }
        return true;
    }
}
