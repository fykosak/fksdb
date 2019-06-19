<?php

namespace FKSDB\Components\Controls\Stalking;

/**
 * Class Login
 * @package FKSDB\Components\Controls\Stalking
 */
class Login extends AbstractStalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->login = $this->modelPerson->getLogin();
        $this->template->setFile(__DIR__ . '/Login.latte');
        $this->template->render();
    }
    /**
     * @return string
     */
    protected function getHeadline(): string {
        return _('Login info');
    }

    /**
     * @return string[]
     */
    protected function getAllowedPermissions(): array {
        return [ AbstractStalkingComponent::PERMISSION_FULL ,AbstractStalkingComponent::PERMISSION_RESTRICT];
    }
}
