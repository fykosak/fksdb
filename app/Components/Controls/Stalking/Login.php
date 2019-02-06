<?php

namespace FKSDB\Components\Controls\Stalking;

/**
 * Class Login
 * @package FKSDB\Components\Controls\Stalking
 */
class Login extends StalkingComponent {

    public function render() {
        $this->beforeRender();
        $this->template->login = $this->modelPerson->getLogin();
        $this->template->setFile(__DIR__ . '/Login.latte');
        $this->template->render();
    }
}
