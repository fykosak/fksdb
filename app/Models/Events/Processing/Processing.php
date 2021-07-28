<?php

namespace FKSDB\Models\Events\Processing;

use FKSDB\Models\Events\Machine\Machine;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Logging\Logger;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

interface Processing {
    /**
     * @param array $states
     * @param ArrayHash $values
     * @param Machine $machine
     * @param Holder $holder
     * @param Logger $logger
     * @param Form|null $form
     * @return array|void
     */
    public function process(array $states, ArrayHash $values, Machine $machine, Holder $holder, Logger $logger, ?Form $form = null): ?array;
}
