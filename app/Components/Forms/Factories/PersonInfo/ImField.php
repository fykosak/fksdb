<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use Nette\Forms\Form;

/**
 * Class ImField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class ImField extends WriteOnlyInput {

    public function __construct() {
        parent::__construct(_('ICQ, Jabber, apod.'));
        $this->addRule(Form::MAX_LENGTH, null, 32);

    }
}
