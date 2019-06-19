<?php

namespace FKSDB\Components\Controls\Stalking;

/**
 * Class Org
 * @package FKSDB\Components\Controls\Stalking
 */
class Org extends AbstractStalkingComponent {

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
        return [self::PERMISSION_BASIC, self::PERMISSION_RESTRICT, self::PERMISSION_FULL];
    }
}
