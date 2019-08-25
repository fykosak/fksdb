<?php

namespace FKSDB\Components\Controls\Stalking;

/**
 * Class EventOrg
 * @package FKSDB\Components\Controls\Stalking
 */
class EventOrg extends AbstractStalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->orgs = $this->modelPerson->getEventOrg();
        $this->template->setFile(__DIR__ . '/EventOrg.latte');
        $this->template->render();
    }
    /**
     * @return string
     */
    protected function getHeadline(): string {
        return _('Event org');
    }

    /**
     * @return string[]
     */
    protected function getAllowedPermissions(): array {
        return [AbstractStalkingComponent::PERMISSION_BASIC, AbstractStalkingComponent::PERMISSION_RESTRICT, AbstractStalkingComponent::PERMISSION_FULL];
    }
}
