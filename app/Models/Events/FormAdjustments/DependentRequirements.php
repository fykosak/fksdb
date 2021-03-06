<?php

namespace FKSDB\Models\Events\FormAdjustments;

use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Forms\Control;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class DependentRequirements extends PairwiseAdjustment {

    protected function processPair(BaseControl $target, Control $prerequisite): void {
        $target->getRules()->addConditionOn($prerequisite, Form::FILLED)->addRule(Form::FILLED, _('Field %label is required.'));
    }
}
