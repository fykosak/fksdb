<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking\Components;

use FKSDB\Components\Controls\Stalking\BaseStalkingComponent;
use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Models\ORM\Models\TaskContributionModel;
use Tracy\Debugger;

class TaskContributionListComponent extends BaseStalkingComponent
{

    final public function render(): void
    {
        if ($this->beforeRender()) {
            /** @var TaskContributionModel $taskContribution */
            $data = [];
            foreach ($this->person->getTaskContributions() as $taskContribution) {
                $key = $taskContribution->task->contest_id . '-' . $taskContribution->task->year . '-' .
                    $taskContribution->task->series . '-' . $taskContribution->task->label;
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
