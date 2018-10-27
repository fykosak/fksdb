<?php

namespace FKSDB\Components\Forms\Factories\Org;


use FKSDB\ORM\ModelContest;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\Form;

class UntilField extends TextInput {
    public function __construct(\YearCalculator $yearCalculator, ModelContest $contest) {
        parent::__construct(_('Do ročníku'));
        $min = $yearCalculator->getFirstYear($contest);
        $max = $yearCalculator->getLastYear($contest);

            $this->addCondition(Form::FILLED)
            ->addRule(Form::NUMERIC)
            ->addRule(Form::RANGE, _('Koncový ročník není v intervalu [%d, %d].'), [$min, $max]);
    }
}
