<?php

use FKSDB\ORM\ModelEvent;
use Nette\Database\Table\Selection;

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
        $row = $this->getTable()->where([
            'fyziklani_task_id' => $taskId,
            'e_fyziklani_team_id' => $teamId
        ])->fetch();
        return $row ? ModelFyziklaniSubmit::createFromTableRow($row) : null;
    }

    /**
     * Syntactic sugar.
     * @param $event ModelEvent
     * @return Selection|null
     */
    public function findAll(ModelEvent $event): Selection {
        return $this->getTable()->where('e_fyziklani_team_id.event_id', $event->event_id);
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

    /**
     * @param ModelEvent $event
     * @param null $lastUpdated
     * @return array
     */
    public function getSubmits(ModelEvent $event, $lastUpdated = null) {
        $query = $this->getTable()->where('e_fyziklani_team.event_id', $event->event_id);
        $submits = [];
        if ($lastUpdated) {
            $query->where('modified >= ?', $lastUpdated);
        }
        foreach ($query as $row) {
            $submit = ModelFyziklaniSubmit::createFromTableRow($row);
            $submits[$submit->fyziklani_submit_id] = $submit->__toArray();
        }
        return $submits;
    }
}
