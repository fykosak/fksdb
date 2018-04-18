<?php

namespace FKSDB\Components\Controls\Stalking;

class PersonHistory extends StalkingComponent {
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
        $this->template->data = [];
        $this->template->data['history'] = $this->modelPerson->related(\DbNames::TAB_PERSON_HISTORY, 'person_id');
        $template->setFile(__DIR__ . '/PersonHistory.latte');
        $template->render();
    }
}
