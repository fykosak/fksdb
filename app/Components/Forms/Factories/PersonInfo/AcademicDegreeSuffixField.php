<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;


use Nette\Forms\Controls\TextInput;

/**
 * Class AcademicDegreeSuffixField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
class AcademicDegreeSuffixField extends TextInput {

    public function __construct() {
        parent::__construct(_('Titul za jménem'));
    }
}
