<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Models\ORM\FieldLevelPermissionValue;
use FKSDB\Models\ORM\Models\Fyziklani\TeamTeacherModel;

class FyziklaniTeamTeacherListComponent extends BaseComponent
{
    final public function render(): void
    {
        if ($this->beforeRender()) {
            /** @var TeamTeacherModel $teacher */
            $data = [];
            foreach ($this->person->getFyziklaniTeachers() as $teacher) {
                $key = $teacher->fyziklani_team->event_id;
                if (!isset($data[$key])) {
                    $data[$key] = [
                        'teams' => [],
                        'event' => $teacher->fyziklani_team->event,
                    ];
                }
                $data[$key]['teams'][] = $teacher->fyziklani_team;
            }
            $this->template->data = $data;
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'fyziklaniTeamTeacher.latte');
        }
    }

    protected function getMinimalPermission(): FieldLevelPermissionValue
    {
        return FieldLevelPermissionValue::Restrict;
    }
}
