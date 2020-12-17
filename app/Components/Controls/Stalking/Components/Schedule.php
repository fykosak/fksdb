<?php

namespace FKSDB\Components\Controls\Stalking\Components;

use FKSDB\Components\Controls\Stalking\StalkingControl;
use FKSDB\Model\DBReflection\FieldLevelPermission;
use FKSDB\Model\ORM\Models\ModelPerson;

/**
 * Class Schedule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Schedule extends StalkingControl {
    public function render(ModelPerson $person, int $userPermissions): void {
        $this->beforeRender($person, _('Schedule during events'), $userPermissions, FieldLevelPermission::ALLOW_RESTRICT);
        $this->template->schedule = $person->getSchedule();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.schedule.latte');
        $this->template->render();
    }
}
