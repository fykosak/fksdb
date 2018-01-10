<?php

namespace FKSDB\Components\Controls\Stalking;

use Nette\Application\UI\Control;

class BaseInfo extends Control {
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
        $this->template->info = $this->modelPerson->getInfo();
        $template->setFile(__DIR__ . '/BaseInfo.latte');
        $template->render();
    }
}
