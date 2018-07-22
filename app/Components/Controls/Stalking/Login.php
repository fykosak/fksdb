<?php

namespace FKSDB\Components\Controls\Stalking;

class Login extends StalkingComponent {

    public function render() {
        $template = $this->template;
        $this->template->login = $this->modelPerson->getLogin();
        $template->setFile(__DIR__ . '/Login.latte');
        $template->render();
    }
}
