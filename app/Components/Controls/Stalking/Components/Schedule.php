<?php


namespace FKSDB\Components\Controls\Stalking;

use FKSDB\ORM\Models\ModelPerson;

/**
 * Class Schedule
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Schedule extends AbstractStalkingComponent {

    public function render(ModelPerson $person, int $userPermissions): void {
        $this->beforeRender($person, $userPermissions);
        $this->template->schedule = $person->getSchedule();
        $this->template->setFile(__DIR__ . '/Schedule.latte');
        $this->template->render();
    }

    /**
     * @return int[]
     */
    protected function getAllowedPermissions(): array {
        return [self::PERMISSION_FULL, self::PERMISSION_RESTRICT];
    }

    protected function getHeadline(): string {
        return _('Schedule during events');
    }
}
