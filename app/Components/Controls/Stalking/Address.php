<?php

namespace FKSDB\Components\Controls\Stalking;

/**
 * Class Address
 * @package FKSDB\Components\Controls\Stalking
 */
class Address extends StalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->MAddress = $this->modelPerson->getMPostContacts();
        $this->template->setFile(__DIR__ . '/Address.latte');
        $this->template->render();
    }

    /**
     * @return string
     */
    protected function getHeadline(): string {
        return _('Address');
    }

    /**
     * @return string[]
     */
    protected function getAllowedPermissions(): array {
        return [StalkingComponent::PERMISSION_FULL ,StalkingComponent::PERMISSION_RESTRICT];
    }
}
