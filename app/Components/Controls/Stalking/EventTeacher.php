<?php

namespace FKSDB\Components\Controls\Stalking;

/**
 * Class EventTeacher
 * @package FKSDB\Components\Controls\Stalking
 */
class EventTeacher extends StalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->teachers = $this->modelPerson->related(\DbNames::TAB_E_FYZIKLANI_TEAM, 'teacher_id');
        $this->template->setFile(__DIR__ . '/EventTeacher.latte');
        $this->template->render();
    }
}
