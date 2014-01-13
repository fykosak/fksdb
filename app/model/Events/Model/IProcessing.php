<?php

namespace Events\Model;

use Events\Machine\Machine;
use Events\SubmitProcessingException;
use Nette\ArrayHash;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IProcessing {

    /**
     * @param ArrayHash $values
     * @param \Events\Model\Machine $machine
     * @param \Events\Model\Holder $holder
     * @return null|array[machineName] => new state
     * @throws SubmitProcessingException
     */
    public function process(ArrayHash $values, Machine $machine, Holder $holder);
}

