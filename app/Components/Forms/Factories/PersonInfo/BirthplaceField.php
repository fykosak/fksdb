<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use Nette\Forms\Form;

/**
 * Class BirthplaceField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class BirthplaceField extends WriteOnlyInput {

    public function __construct() {
        parent::__construct(_('Místo narození'));
        $this->setOption('description', _('Město a okres (kvůli diplomům).'));
        $this->addRule(Form::MAX_LENGTH, null, 255);
    }
}
