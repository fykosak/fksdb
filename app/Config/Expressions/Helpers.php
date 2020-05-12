<?php

namespace FKSDB\Config\Expressions;

use FKSDB\Expressions\Comparison\Le;
use FKSDB\Expressions\Comparison\Leq;
use FKSDB\Expressions\Logic\LogicAnd;
use FKSDB\Expressions\Logic\Not;
use FKSDB\Expressions\Logic\LogicOr;
use FKSDB\Expressions\Predicates\After;
use FKSDB\Expressions\Predicates\Before;
use Nette\DI\Container;
use Nette\DI\Helpers as DIHelpers;
use Nette\DI\Statement;
use Nette\Reflection\ClassType;
use Nette\Utils\Arrays;
use Traversable;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Helpers {

    private static $semanticMap = [
        'and' => LogicAnd::class,
        'or' => LogicOr::class,
        'neg' => Not::class,
        'after' => After::class,
        'before' => Before::class,
        'le' => Le::class,
        'leq' => Leq::class,
    ];

    /**
     * @param $semanticMap
     */
    public static function registerSemantic($semanticMap) {
        self::$semanticMap += $semanticMap;
    }

    /**
     * Transforms into dynamic expression tree built from FKSDB\Expressions\*.
     *
     * @param Statement|mixed $expression
     * @return mixed|Statement
     */
    public static function statementFromExpression($expression) {
        if ($expression instanceof Statement) {
            // Debugger::barDump($expression, 'B');

            $arguments = [];
            foreach ($expression->arguments as $attribute) {
                $arguments[] = self::statementFromExpression($attribute);
            }
            $class = Arrays::get(self::$semanticMap, $expression->entity, $expression->entity);
            if (function_exists($class)) { // workaround for Nette interpretation of entities
                $class = ['', $class];
            }
            //    Debugger::barDump(new Statement($class, $arguments), 'A');
            return new Statement($class, $arguments);
        } elseif (is_array($expression)) {
        //    Debugger::barDump($expression, 'Ar');
            return array_map(function ($subExpresion) {
                return self::statementFromExpression($subExpresion);
            }, $expression);

        } else {
            return $expression;
        }
    }

    /**
     * Transforms and evalutes the expression during runtime.
     *
     * @param mixed $expression
     * @param Container $container
     * @return mixed
     */
    public static function evalExpression($expression, Container $container) {
       if ($expression instanceof Statement) {
            $arguments = [];
            foreach ($expression->arguments as $attribute) {
                if ($attribute === '...') {
                    continue;
                }
                $arguments[] = self::evalExpression($attribute, $container);
            }

            $entity = Arrays::get(self::$semanticMap, $expression->entity, $expression->entity);
            if (function_exists($entity)) {
                return call_user_func_array($entity, $arguments);
            } else {
                $rc = ClassType::from($entity);
                return $rc->newInstanceArgs(DIHelpers::autowireArguments($rc->getConstructor(), $arguments, $container));
            }
        } else {
            return $expression;
        }
    }

    /**
     * @param $expressionArray
     * @param Container $container
     * @return array|mixed
     */
    public static function evalExpressionArray($expressionArray, Container $container) {
        if ($expressionArray instanceof Traversable || is_array($expressionArray)) {
            $result = [];
            foreach ($expressionArray as $key => $expression) {
                $result[$key] = self::evalExpressionArray($expression, $container);
            }
            return $result;
        } else {
            return self::evalExpression($expressionArray, $container);
        }
    }

}
