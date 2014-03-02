<?php

namespace Events\Processings;

use Events\Machine\Machine;
use Events\Model\Holder\Holder;
use FKS\Logging\ILogger;
use Nette\ArrayHash;
use Nette\Forms\Form;
use Submits\ProcessingException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IProcessing {

    /**
     * @throws ProcessingException
     */
    public function process($states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, Form $form = null);
}

