<?php

namespace Persons\Deduplication\MergeStrategy;

use Nette\InvalidArgumentException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
interface IMergeStrategy {

    /**
     * 
     * @param mixed $trunk
     * @param mixed $merged
     * @throws CannotMergeException
     */
    public function mergeValues($trunk, $merged);
}

class CannotMergeException extends InvalidArgumentException {
    
}
