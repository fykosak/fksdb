<?php

namespace FKSDB\Models\Expressions;

use FKSDB\Models\Expressions\Comparison\Le;
use FKSDB\Models\Expressions\Comparison\Leq;
use FKSDB\Models\Expressions\Logic\LogicAnd;
use FKSDB\Models\Expressions\Logic\LogicOr;
use FKSDB\Models\Expressions\Logic\Not;
use FKSDB\Models\Expressions\Predicates\After;
use FKSDB\Models\Expressions\Predicates\Before;
use Nette\DI\Container;
use Nette\DI\Definitions\Statement;
use Nette\DI\ServiceCreationException;
use Nette\NotImplementedException;

class Helpers
{

    /** @var string[] */
    private static array $semanticMap = [
        'and' => LogicAnd::class,
        'or' => LogicOr::class,
        'neg' => Not::class,
        'after' => After::class,
        'before' => Before::class,
        'le' => Le::class,
        'leq' => Leq::class,
    ];

    public static function registerSemantic(array $semanticMap): void
    {
        self::$semanticMap += $semanticMap;
    }

    /**
     * Transforms into dynamic expression tree built from FKSDB\Expressions\*.
     *
     * @param Statement|mixed $expression
     * @return array|Statement|mixed
     */
    public static function statementFromExpression($expression)
    {
        if ($expression instanceof Statement) {
            $arguments = [];
            foreach ($expression->arguments as $attribute) {
                $arguments[] = self::statementFromExpression($attribute);
            }
            $class = $expression->entity;
            if (!is_array($expression->entity)) {
                $class = self::$semanticMap[$expression->entity] ?? $class;
                if (function_exists($class)) { // workaround for Nette interpretation of entities
                    $class = ['', $class];
                }
            }

            return new Statement($class, $arguments);
        } elseif (is_array($expression)) {
            return array_map(fn($subExpresion) => self::statementFromExpression($subExpresion), $expression);
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
     * @throws ServiceCreationException
     * @throws \ReflectionException
     */
    public static function evalExpression($expression, Container $container)
    {
        if ($expression instanceof Statement) {
            $arguments = [];
            foreach ($expression->arguments as $attribute) {
                if ($attribute === '...') {
                    continue;
                }
                $arguments[] = self::evalExpression($attribute, $container);
            }
            $entity = self::$semanticMap[$expression->entity] ?? $expression->entity;
            if (function_exists($entity)) {
                return $entity(...$arguments);
            } else {
                throw new NotImplementedException();
                /*  $rc = ClassType::from($entity);
                  return $rc->newInstanceArgs(Resolver::autowireArguments($rc->getConstructor(), $arguments, function (string $type, bool $single) use ($container) {
                      return $this->getByType($type);
                  }));*/
                // TODO!!!
            }
        } else {
            return $expression;
        }
    }

    /**
     * @param mixed $expressionArray
     * @param Container $container
     * @return mixed
     * @throws \ReflectionException
     */
    public static function evalExpressionArray($expressionArray, Container $container)
    {
        if (is_iterable($expressionArray)) {
            $result = [];
            foreach ($expressionArray as $key => $expression) {
                $result[$key] = self::evalExpressionArray($expression, $container);
            }
            return $result;
        } else {
            return self::evalExpression($expressionArray, $container);
        }
    }

    /**
     * @param $statement
     * @return Statement|string
     */
    public static function translate($statement)
    {
        if ($statement instanceof Statement && $statement->entity === '_') {
            return _(...$statement->arguments);
        }
        return $statement;
    }
}
