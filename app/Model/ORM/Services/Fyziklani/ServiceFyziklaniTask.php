<?php

namespace FKSDB\Model\ORM\Services\Fyziklani;

use FKSDB\Model\ORM\DbNames;
use FKSDB\Model\ORM\Models\Fyziklani\ModelFyziklaniTask;
use FKSDB\Model\ORM\Models\ModelEvent;
use FKSDB\Model\ORM\Services\AbstractServiceSingle;
use FKSDB\Model\ORM\Services\DeprecatedLazyService;
use FKSDB\Model\ORM\Tables\TypedTableSelection;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * @author Lukáš Timko <lukast@fykos.cz>
 */
class ServiceFyziklaniTask extends AbstractServiceSingle {
    use DeprecatedLazyService;

    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_FYZIKLANI_TASK, ModelFyziklaniTask::class);
    }

    public function findByLabel(string $label, ModelEvent $event): ?ModelFyziklaniTask {
        /** @var ModelFyziklaniTask $result */
        $result = $this->getTable()->where([
            'label' => $label,
            'event_id' => $event->event_id,
        ])->fetch();
        return $result ?: null;
    }

    public function findAll(ModelEvent $event): TypedTableSelection {
        return $this->getTable()->where('event_id', $event->event_id);
    }

    /**
     * @param ModelEvent $event
     * @param bool $hideName
     * @return ModelFyziklaniTask[]
     */
    public function getTasksAsArray(ModelEvent $event, bool $hideName = false): array {
        $tasks = [];

        foreach ($this->findAll($event)->order('label') as $row) {
            $model = ModelFyziklaniTask::createFromActiveRow($row);
            $tasks[] = $model->__toArray($hideName);
        }
        return $tasks;
    }
}
