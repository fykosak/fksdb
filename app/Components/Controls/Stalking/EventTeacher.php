<?php

namespace FKSDB\Components\Controls\Stalking;

class EventTeacher extends StalkingComponent {

    public function render() {
        $template = $this->template;
        $this->template->teams = $this->modelPerson->related(\DbNames::TAB_E_FYZIKLANI_TEAM, 'teacher_id');
        $template->setFile(__DIR__ . '/EventTeacher.latte');
        $template->render();
    }
}