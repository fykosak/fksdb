<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\FormAdjustments;

use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Forms\Control;

class DependentRequirements extends PairwiseAdjustment
{
    protected function processPair(BaseControl $target, Control $prerequisite): void
    {
        $target->getRules()->addConditionOn($prerequisite, Form::FILLED)->addRule(
            Form::FILLED,
            _('Field %label is required.')
        );
    }
}
