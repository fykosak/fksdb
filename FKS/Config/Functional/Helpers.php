<?php

namespace FKS\Config\Functional;

use Nette\DI\Statement;
use stdClass;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class Helpers {

    public static function createConditionStatement($condition) {
        if (!$condition instanceof stdClass) {
            return $condition;
        }

        $arguments = array();
        $arguments[] = $condition->value; // operator
        foreach ($condition->attributes as $attribute) {
            $arguments[] = $this->createConditionStatement($attribute);
        }

        return new Statement('FKS\Config\Functional\LogicCondition', $arguments);
    }

}
