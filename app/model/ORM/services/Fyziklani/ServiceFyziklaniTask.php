<?php

use FKSDB\ORM\ModelEvent;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceFyziklaniTask extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_FYZIKLANI_TASK;
    protected $modelClassName = 'ModelFyziklaniTask';

    /**
     * Syntactic sugar.
     * @param $label string
     * @param $event ModelEvent
     * @return ModelFyziklaniTask|null
     */
    public function findByLabel(string $label, ModelEvent $event) {
        /**
         * @var $result ModelFyziklaniTask
         */
        $result = $this->getTable()->where([
            'label' => $label,
            'event_id' => $event->event_id,
        ])->fetch();

        return $result ? ModelFyziklaniTask::createFromTableRow($result) : null;
    }

    /**
     * Syntactic sugar.
     * @param $event ModelEvent
     * @return \Nette\Database\Table\Selection|null
     */
    public function findAll(ModelEvent $event) {
        $result = $this->getTable()->where('event_id', $event->event_id);
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
     * @param ModelEvent $event
     * @param bool $hideName
     * @return array
     */
    public function getTasksAsArray(ModelEvent $event, bool $hideName = false): array {
        $tasks = [];

        foreach ($this->findAll($event)->order('label') as $row) {
            $model = ModelFyziklaniTask::createFromTableRow($row);
            $tasks[] = $model->__toArray($hideName);
        }
        return $tasks;
    }

}
