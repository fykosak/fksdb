<?php

namespace FKSDB\Events\FormAdjustments;

use Nette\Forms\Form;
use Nette\Forms\IControl;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class DependentVisibility extends PairwiseAdjustment {
    /**
     * @param IControl $target
     * @param IControl $prerequisite
     * @return void
     */
    protected function processPair(IControl $target, IControl $prerequisite) {
        $target->getRules()->addConditionOn($prerequisite, Form::FILLED)->toggle($target->getHtmlId() . '-pair');
    }

}
