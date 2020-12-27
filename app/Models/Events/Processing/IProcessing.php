<?php

namespace FKSDB\Models\Events\Processing;

use FKSDB\Models\Events\Machine\Machine;
use FKSDB\Models\Events\Model\Holder\Holder;
use Fykosak\Utils\Logging\ILogger;
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
