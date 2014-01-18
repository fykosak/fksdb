<?php

namespace Events\Model;

use Events\Machine\Machine;
use Nette\Forms\Form;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IFormAdjustment {

    /**
     * @param Form $values
     * @param Machine $machine
     * @param \Events\Model\Holder $holder
     */
    public function adjust(Form $form, Machine $machine, Holder $holder);
}

