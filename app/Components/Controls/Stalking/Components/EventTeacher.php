<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\ORM\DbNames;

/**
 * Class EventTeacher
 * @package FKSDB\Components\Controls\Stalking
 */
class EventTeacher extends AbstractStalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->teachers = $this->modelPerson->related(DbNames::TAB_E_FYZIKLANI_TEAM, 'teacher_id');
        $this->template->setFile(__DIR__ . '/EventTeacher.latte');
        $this->template->render();
    }

    /**
     * @return string
     */
    protected function getHeadline(): string {
        return _('Event teacher');
    }

    /**
     * @return array
     */
    protected function getAllowedPermissions(): array {
        return [self::PERMISSION_BASIC, self::PERMISSION_RESTRICT, self::PERMISSION_FULL];
    }
}
