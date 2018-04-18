<?php

namespace FKSDB\Components\Controls\Stalking;

class EventParticipant extends StalkingComponent {
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
        $this->template->participants = $this->modelPerson->getEventParticipant();
        $template->setFile(__DIR__ . '/EventParticipant.latte');
        $template->render();
    }
}
