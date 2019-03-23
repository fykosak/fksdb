<?php

namespace FKSDB\Components\Controls\Stalking;

/**
 * Class Org
 * @package FKSDB\Components\Controls\Stalking
 */
class Org extends StalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->orgs = $this->modelPerson->getOrgs();
        $this->template->setFile(__DIR__ . '/Org.latte');
        $this->template->render();
    }
    /**
     * @return string
     */
    protected function getHeadline(): string {
        return _('Org');
    }

    /**
     * @return string[]
     */
    protected function getAllowedPermissions(): array {
        return [StalkingComponent::PERMISSION_BASIC, StalkingComponent::PERMISSION_RESTRICT, StalkingComponent::PERMISSION_FULL];
    }
}
