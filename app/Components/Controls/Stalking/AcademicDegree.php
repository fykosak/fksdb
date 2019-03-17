<?php

namespace FKSDB\Components\Controls\Stalking;

/**
 * Class BaseInfo
 * @package FKSDB\Components\Controls\Stalking
 */
class AcademicDegree extends StalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->info = $this->modelPerson->getInfo();
        $this->template->setFile(__DIR__ . '/AcademicDegree.latte');
        $this->template->render();
    }
    /**
     * @return string
     */
    protected function getHeadline(): string {
        return _('Academic degree');
    }

    /**
     * @return string[]
     */
    protected function getAllowedPermissions(): array {
        return [StalkingComponent::PERMISSION_FULL, StalkingComponent::PERMISSION_RESTRICT];
    }
}
