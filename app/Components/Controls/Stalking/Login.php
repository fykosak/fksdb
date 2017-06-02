<?php

namespace FKSDB\Components\Controls\Stalking;

use Nette\Application\UI\Control;

class Login extends Control {
    private $mode;
    /**
     * @var \ModelPerson;
     */
    private $modelPerson;

    public function __construct(\ModelPerson $modelPerson, $mode = null) {
        parent::__construct();
        $this->mode = $mode;
        $this->modelPerson = $modelPerson;
    }

    public function render() {
        $template = $this->template;
        $this->template->login = $this->modelPerson->getLogin();
        $template->setFile(__DIR__ . '/Login.latte');
        $template->render();
    }
}
