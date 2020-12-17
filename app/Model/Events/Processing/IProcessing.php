<?php

namespace FKSDB\Model\Events\Processing;

use FKSDB\Model\Events\Machine\Machine;
use FKSDB\Model\Events\Model\Holder\Holder;
use FKSDB\Model\Logging\ILogger;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IProcessing {
    /**
     * @param array $states
     * @param ArrayHash $values
     * @param Machine $machine
     * @param Holder $holder
     * @param ILogger $logger
     * @param Form|null $form
     * @return array|void
     */
    public function process(array $states, ArrayHash $values, Machine $machine, Holder $holder, ILogger $logger, ?Form $form = null);
}