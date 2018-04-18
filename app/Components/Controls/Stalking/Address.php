<?php

namespace FKSDB\Components\Controls\Stalking;

class Address extends StalkingComponent {
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
        $this->template->MAddress = $this->modelPerson->getMPostContacts();
        $template->setFile(__DIR__ . '/Address.latte');
        $template->render();
    }
}
