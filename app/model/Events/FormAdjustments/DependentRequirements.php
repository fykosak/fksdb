<?php

namespace Events\FormAdjustments;

use Nette\Forms\Form;
use Nette\Forms\IControl;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class DependentRequirements extends AbstractAdjustment {

    protected function processPair(IControl $target, IControl $prerequisity) {
        $target->getRules()->addConditionOn($prerequisity, Form::FILLED)->addRule(Form::FILLED, _('Pole %label je třeba vyplnit.'));
    }

}
