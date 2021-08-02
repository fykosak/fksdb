<?php

namespace FKSDB\Models\Events\FormAdjustments;

use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Forms\Control;

class DependentVisibility extends PairwiseAdjustment
{

    protected function processPair(BaseControl $target, Control $prerequisite): void
    {
        $target->getRules()->addConditionOn($prerequisite, Form::FILLED)->toggle($target->getHtmlId() . '-pair');
    }
}
