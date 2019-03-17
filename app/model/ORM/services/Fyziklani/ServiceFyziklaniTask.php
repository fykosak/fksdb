<?php

namespace FKSDB\ORM\Services\Fyziklani;

use AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Database\Table\Selection;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceFyziklaniTask extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_FYZIKLANI_TASK;
    protected $modelClassName = 'FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTask';

    /**
     * Syntactic sugar.
     * @param string $label
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @return ModelFyziklaniTask|null
     */
    public function findByLabel(string $label, ModelEvent $event) {
        /**
         * @var ModelFyziklaniTask $result
         */
        $result = $this->getTable()->where([
            'label' => $label,
            'event_id' => $event->event_id,
        ])->fetch();

        return $result ? ModelFyziklaniTask::createFromTableRow($result) : null;
    }

    /**
     * Syntactic sugar.
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @return Selection
     */
    public function findAll(ModelEvent $event): Selection {
        return $this->getTable()->where('event_id', $event->event_id);
    }

    /**
     * @param \FKSDB\ORM\Models\ModelEvent $event
     * @param bool $hideName
     * @return ModelFyziklaniTask[]
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
