<?php

namespace FKSDB\Components\Controls\Stalking;

/**
 * Class Flag
 * @package FKSDB\Components\Controls\Stalking
 */
class Flag extends StalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->flags = $this->modelPerson->getMPersonHasFlags();
        $this->template->setFile(__DIR__ . '/Flag.latte');
        $this->template->render();
    }

    /**
     * @return string
     */
    protected function getHeadline(): string {
        return _('Flags');
    }

    /**
     * @return string[]
     */
    protected function getAllowedPermissions(): array {
        return [StalkingComponent::PERMISSION_FULL, StalkingComponent::PERMISSION_RESTRICT];
    }
}
