<?php

namespace FKSDB\Events\FormAdjustments;

use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Form;
use Nette\Forms\IControl;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class DependentVisibility extends PairwiseAdjustment {
    /**
     * @param BaseControl|IControl $target
     * @param IControl $prerequisite
     * @return void
     */
    protected function processPair(IControl $target, IControl $prerequisite): void {
        $target->getRules()->addConditionOn($prerequisite, Form::FILLED)->toggle($target->getHtmlId() . '-pair');
    }
}
