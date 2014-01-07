<?php

namespace FKS\Config\Expressions;

use Nette\DI\Statement;
use stdClass;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class Helpers {

    private static $semanticMaps = array(
        'and' => 'FKS\Expressions\Logic\And_',
        'or' => 'FKS\Expressions\Logic\or_',
    );

    /**
     * Transforms into dynamic expression tree built from FKS\Expressions\*.
     * 
     * @param stdClass $expression
     * @return mixed|\Nette\DI\Statement
     */
    public static function statementFromExpression($expression) {
        if (!$expression instanceof stdClass) {
            return $expression;
        }

        $arguments = array();
        foreach ($expression->attributes as $attribute) {
            $arguments[] = self::statementFromExpression($attribute);
        }

        $class = self::$semanticMaps[$expression->value];
        return new Statement($class, $arguments);
    }

}
