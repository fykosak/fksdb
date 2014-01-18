<?php

namespace Events\Spec\Fyziklani;

use Events\Machine\Machine;
use Events\Model\Holder\Holder;
use Events\Processings\IProcessing;
use Nette\ArrayHash;
use Nette\Object;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class CategoryProcessing extends Object implements IProcessing {

    public function process(ArrayHash $values, Machine $machine, Holder $holder) {
        $values['team']['category'] = 'A'; //TODO
    }

}
