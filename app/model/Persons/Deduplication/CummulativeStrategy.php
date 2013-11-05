<?php

namespace Persons\Deduplication;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class CummulativeStrategy implements IMergeStrategy {

    public function mergeValues($trunk, $merged) {
        if ($merged === null) {
            return $trunk;
        }
        if ($trunk === null) {
            return $merged;
        }
        if ($trunk === $merged) {
            return $trunk;
        }
        throw new CannotMergeException();
    }

}
