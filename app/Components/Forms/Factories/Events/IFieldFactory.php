<?php

namespace FKSDB\Components\Forms\Factories\Events;

use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\Model\Field;
use Nette\ComponentModel\Component;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IFieldFactory {

    /**
     * 
     * @param Field $field
     * @param Machine $machine
     * @return Component
     */
    public function create(Field $field, BaseMachine $machine);
}
