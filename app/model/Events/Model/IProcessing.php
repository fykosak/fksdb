<?php

namespace Events\Model;

use Events\SubmitProcessingException;
use Nette\ArrayHash;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IProcessing {

    /**
     * 
     * @param Holder $holder
     * @param ArrayHash $values
     * @return null|array[machineName] => new state
     * @throws SubmitProcessingException
     */
    public function process(Holder2 $holder, ArrayHash $values);
}

