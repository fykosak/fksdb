<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Models\ORM\Models\TaskContributionModel;

class TaskContributionListComponent extends BaseComponent
{

    final public function render(): void
    {
        if ($this->beforeRender()) {
            /** @var TaskContributionModel $taskContribution */
            $data = [];
            foreach ($this->person->getTaskContributions() as $taskContribution) {
                $key = $taskContribution->task->createUniqueKey();
                if (!isset($data[$key])) {
                    $data[$key] = [
                        'contributions' => [],
                        'task' => $taskContribution->task,
                    ];
                }
                $data[$key]['contributions'][] = $taskContribution;
            }
            $this->template->data = $data;
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'taskContribution.latte');
        }
    }

    protected function getMinimalPermissions(): FieldLevelPermissionValue
    {
        return FieldLevelPermissionValue::Restrict;
    }
}
