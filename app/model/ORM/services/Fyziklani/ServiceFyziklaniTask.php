<?php

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceFyziklaniTask extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_FYZIKLANI_TASK;
    protected $modelClassName = 'ModelFyziklaniTask';

    /**
     * Syntactic sugar.
     * @param $label string
     * @param $eventId integer
     * @return ModelFyziklaniTask|null
     */
    public function findByLabel($label, $eventId) {
        if (!$label || !$eventId) {
            return null;
        }
        /**
         * @var $result ModelFyziklaniTask
         */
        $result = $this->getTable()->where(array(
            'label' => $label,
            'event_id' => $eventId
        ))->fetch();
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
            $result->where('event_id', $eventId);
        }
        return $result ?: null;
    }

    public function taskLabelToTaskId($taskLabel, $eventId) {
        /**
         * @var $task ModelFyziklaniTask
         */
        $task = $this->findByLabel($taskLabel, $eventId);
        if ($task) {
            return $task->fyziklani_task_id;
        }
        return false;
    }

    /**
     * @param integer $eventId
     * @param bool $injectName
     * @return array
     */
    public function getTasks($eventId, $injectName = true) {
        $tasks = [];
        /**
         * @var $row ModelFyziklaniTask
         */
        foreach ($this->findAll($eventId)->order('label') as $row) {
            $tasks[] = $row->__toArray();
        }
        return $tasks;
    }

}
