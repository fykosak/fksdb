<?php

namespace FKSDB\Components\Controls\Stalking;

/**
 * Class EventParticipant
 * @package FKSDB\Components\Controls\Stalking
 */
class EventParticipant extends AbstractStalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->participants = $this->modelPerson->getEventParticipant();
        $this->template->setFile(__DIR__ . '/EventParticipant.latte');
        $this->template->render();
    }
    /**
     * @return string
     */
    protected function getHeadline(): string {
        return _('Event participant');
    }

    /**
     * @return string[]
     */
    protected function getAllowedPermissions(): array {
        return [self::PERMISSION_BASIC, self::PERMISSION_RESTRICT, self::PERMISSION_FULL];
    }
}
