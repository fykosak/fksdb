<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKS\Components\Forms\Controls\WriteonlyInput;
use Nette\Forms\Form;

class BirthplaceField extends WriteonlyInput implements \IReactField {
    use \ReactFieldDefinition;

    public function __construct() {
        parent::__construct(_('Místo narození'));
        $this->setOption('description', _('Město a okres (kvůli diplomům).'));
        $this->addRule(Form::MAX_LENGTH, null, 255);
    }

    public function getReactDefinition(): \ReactField {
        return $this->createReactDefinition();
    }
}
