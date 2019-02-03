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
     * @param int $taskId
     * @param int $teamId integer
     * @return ModelFyziklaniSubmit|null
     */
    public function findByTaskAndTeam(int $taskId, int $teamId) {
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
     * @return Selection
     */
    public function findAll(ModelEvent $event): Selection {
        return $this->getTable()->where('e_fyziklani_team_id.event_id', $event->event_id);
    }

    /**
     * @param int $taskId
     * @param int $teamId
     * @return bool
     */
    public function submitExist(int $taskId, int $teamId): bool {
        $submit = $this->findByTaskAndTeam($taskId, $teamId);
        if (is_null($submit)) {
            return false;
        }
        if (is_null($submit->points)) {
            return false;
        }
        return true;
    }

    /**
     * @param ModelEvent $event
     * @param null $lastUpdated
     * @return array
     */
    public function getSubmitsAsArray(ModelEvent $event, $lastUpdated = null): array {
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
