<?php


namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\ORM\Models\ModelPerson;

/**
 * Class Schedule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Schedule extends AbstractStalkingComponent {
    /**
     * @param ModelPerson $person
     * @param int $userPermissions
     * @return void
     */
    public function render(ModelPerson $person, int $userPermissions) {
        $this->beforeRender($person, $userPermissions);
        $this->template->schedule = $person->getSchedule();
        $this->template->setFile(__DIR__ . '/Schedule.latte');
        $this->template->render();
    }

    protected function getMinimalPermissions(): int {
        return FieldLevelPermission::ALLOW_RESTRICT;
    }

    protected function getHeadline(): string {
        return _('Schedule during events');
    }
}
