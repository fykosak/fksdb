<?php

namespace FKSDB\Components\Controls\Stalking;

class Contestant extends StalkingComponent {
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
        $this->template->contestants = $this->modelPerson->getContestants();
        $template->setFile(__DIR__ . '/Contestant.latte');
        $template->render();
    }
}
