<?php


namespace FKSDB\Components\Controls\Stalking;

/**
 * Class Schedule
 * @package FKSDB\Components\Controls\Stalking
 */
class Schedule extends StalkingComponent {
    public function render() {
        $this->beforeRender();
        $this->template->schedule = $this->modelPerson->getSchedule();
        $this->template->setFile(__DIR__ . '/Schedule.latte');
        $this->template->render();
    }

    /**
     * @return array
     */
    protected function getAllowedPermissions(): array {
        return [StalkingComponent::PERMISSION_FULL, StalkingComponent::PERMISSION_RESTRICT];
    }

    /**
     * @return string
     */
    protected function getHeadline(): string {
        return _('Schedule during events');
    }
}
