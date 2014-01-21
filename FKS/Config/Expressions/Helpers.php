<?php

namespace FKS\Config\Expressions;

use Nette\DI\Statement;
use Nette\Utils\Arrays;
use stdClass;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class Helpers {

    private static $semanticMap = array(
        'and' => 'FKS\Expressions\Logic\And_',
        'or' => 'FKS\Expressions\Logic\Or_',
        'neg' => 'FKS\Expressions\Logic\Not',
        'after' => 'FKS\Expressions\Predicates\After',
        'before' => 'FKS\Expressions\Predicates\Before',
    );

    public static function registerSemantic($semanticMap) {
        self::$semanticMap += $semanticMap;
    }

    /**
     * Transforms into dynamic expression tree built from FKS\Expressions\*.
     * 
     * @param stdClass $expression
     * @return mixed|Statement
     */
    public static function statementFromExpression($expression) {
        if (!$expression instanceof stdClass) {
            return $expression;
        }

        $arguments = array();
        foreach ($expression->attributes as $attribute) {
            if ($attribute === '...') {
                continue;
            }
            $arguments[] = self::statementFromExpression($attribute);
        }

        $class = Arrays::get(self::$semanticMap, $expression->value, $expression->value);
        return new Statement($class, $arguments);
    }

}
