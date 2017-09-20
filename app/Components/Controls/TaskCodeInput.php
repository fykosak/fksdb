<?php
namespace FKSDB\Components\Controls;

use \Nette\Forms\Controls\TextInput;
use \Nette\Application\UI\Form;
use Nette\Utils\Json;

class TaskCodeInput extends TextInput {
    
    public function __construct($label = null, $cols = null, $maxLength = null) {
        parent::__construct($label, $cols, $maxLength);
        $this->setHtmlId('taskcode');
        $this->addRule(Form::PATTERN, _('Nesprávný tvar.'), '[0-9]{6}[A-Z]{2}[0-9]');
    }

    public function setTeams($teams) {
        return $this->setAttribute('data-teams', Json::encode($teams));
    }

    public function setTasks($tasks) {
        return $this->setAttribute('data-tasks', Json::encode($tasks));
    }
}
