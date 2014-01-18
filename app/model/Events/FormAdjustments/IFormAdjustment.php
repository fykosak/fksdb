<?php

namespace Events\FormAdjustments;

use Events\Machine\Machine;
use Events\Model\Holder\Holder;
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
     * @param Holder $holder
     */
    public function adjust(Form $form, Machine $machine, Holder $holder);
}

