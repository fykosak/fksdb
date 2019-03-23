<?php

namespace FKSDB\Components\Controls\Stalking;

/**
 * Class ContactInfo
 * @package FKSDB\Components\Controls\Stalking
 */
class ContactInfo extends StalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->info = $this->modelPerson->getInfo();
        $this->template->setFile(__DIR__ . '/ContactInfo.latte');
        $this->template->render();
    }

    /**
     * @return string
     */
    protected function getHeadline(): string {
        return _('Contact info');
    }

    /**
     * @return string[]
     */
    protected function getAllowedPermissions(): array {
        return [StalkingComponent::PERMISSION_FULL, StalkingComponent::PERMISSION_RESTRICT];
    }
}
