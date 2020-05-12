<?php


namespace FKSDB\Components\Controls\Stalking;

use FKSDB\ORM\Models\ModelPerson;

/**
 * Class Schedule
 * @package FKSDB\Components\Controls\Stalking
 */
class Schedule extends AbstractStalkingComponent {
    /**
     * @param ModelPerson $person
     * @param int $userPermissions
     */
    public function render(ModelPerson $person, int $userPermissions) {
        $this->beforeRender($person, $userPermissions);
        $this->template->schedule = $person->getSchedule();
        $this->template->setFile(__DIR__ . '/Schedule.latte');
        $this->template->render();
    }

    /**
     * @return array
     */
    protected function getAllowedPermissions(): array {
        return [AbstractStalkingComponent::PERMISSION_FULL, AbstractStalkingComponent::PERMISSION_RESTRICT];
    }

    /**
     * @return string
     */
    protected function getHeadline(): string {
        return _('Schedule during events');
    }
}
