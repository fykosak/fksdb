<?php

namespace FKSDB\Events\Processings;

use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Logging\ILogger;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IProcessing {

    /**
     * @param $states
     * @param ArrayHash $values
     * @param Machine $machine
     * @param Holder $holder
     * @param ILogger $logger
     * @param Form|null $form
     */
    public function process($states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, Form $form = null);
}

