<?php

namespace FKSDB\Components\Controls\Stalking;

/**
 * Class Contestant
 * @package FKSDB\Components\Controls\Stalking
 */
class Contestant extends StalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->contestants = $this->modelPerson->getContestants();
        $this->template->setFile(__DIR__ . '/Contestant.latte');
        $this->template->render();
    }
    /**
     * @return string
     */
    protected function getHeadline(): string {
        return _('Contestant');
    }

    /**
     * @return string[]
     */
    protected function getAllowedPermissions(): array {
        return [StalkingComponent::PERMISSION_BASIC, StalkingComponent::PERMISSION_RESTRICT, StalkingComponent::PERMISSION_FULL];
    }
}
