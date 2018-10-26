<?php

namespace FKSDB\Components\Forms\Factories\Org;


use FKSDB\ORM\ModelContest;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

class SinceField extends TextInput {
    public function __construct(\YearCalculator $yearCalculator, ModelContest $contest) {
        parent::__construct(_('Od ročníku'));
        $min = $yearCalculator->getFirstYear($contest);
        $max = $yearCalculator->getLastYear($contest);
        $this->addRule(Form::NUMERIC);
        $this->addRule(Form::FILLED);
        $this->addRule(Form::RANGE, _('Počáteční ročník není v intervalu [%d, %d].'), [$min, $max]);
    }
}
