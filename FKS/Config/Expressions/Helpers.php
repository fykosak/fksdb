<?php

namespace FKS\Config\Expressions;

use Nette\DI\Statement;
use Nette\Utils\Arrays;
use ReflectionClass;
use stdClass;
use Traversable;

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
        'le' => 'FKS\Expressions\Comparison\Le',
        'leq' => 'FKS\Expressions\Comparison\Leq',
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
        if (function_exists($class)) { // workaround for Nette interpretation of entities
            $class = array('', $class);
        }
        return new Statement($class, $arguments);
    }

    /**
     * Transforms and evalutes the expression during runtime.
     * 
     * @param mixed $expression
     * @return mixed
     */
    public static function evalExpression($expression) {
        if (!$expression instanceof stdClass) {
            return $expression;
        }

        $arguments = array();
        foreach ($expression->attributes as $attribute) {
            if ($attribute === '...') {
                continue;
            }
            $arguments[] = self::evalExpression($attribute);
        }

        $entity = Arrays::get(self::$semanticMap, $expression->value, $expression->value);
        if (function_exists($entity)) {
            return call_user_func_array($entity, $arguments);
        } else {
            $rc = new ReflectionClass($entity);
            return $rc->newInstanceArgs($arguments);
        }
    }

    public static function evalExpressionArray($expressionArray) {
        if ($expressionArray instanceof Traversable || is_array($expressionArray)) {
            $result = array();
            foreach ($expressionArray as $key => $expression) {
                $result[$key] = self::evalExpressionArray($expression);
            }
            return $result;
        } else {
            return self::evalExpression($expressionArray);
        }
    }

}
