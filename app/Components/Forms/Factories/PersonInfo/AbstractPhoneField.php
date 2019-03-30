<?php

namespace FKSDB\Components\Forms\Factories\PersonInfo;

use FKSDB\Components\Controls\PhoneNumber\PhoneNumberFactory;
use FKSDB\Components\Forms\Controls\WriteOnlyInput;
use Nette\Forms\Form;

/**
 * Class IPhoneField
 * @package FKSDB\Components\Forms\Factories\PersonInfo
 */
abstract class AbstractPhoneField extends WriteOnlyInput {

    /**
     * IPhoneField constructor.
     * @param $label
     */
    public function __construct($label) {
        parent::__construct($label);
        $this->setAttribute("placeholder", _('ve tvaru +420123456789'));
        $this->addRule(Form::MAX_LENGTH, null, 32);
        $this->addCondition(Form::FILLED)
            ->addRule(PhoneNumberFactory::getFormValidationCallback(), _('Phone number is not valid. Please use internation format, starting with "+"'));
        // ->addRule(Form::REGEXP, _('%label smí obsahovat jen číslice a musí být v mezinárodím tvaru začínajícím +421 nebo +420.'), '/\+42[01]\d{9}/');
    }
}
