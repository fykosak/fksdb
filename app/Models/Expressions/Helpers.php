<?php

declare(strict_types=1);

namespace FKSDB\Models\Expressions;

use FKSDB\Models\Expressions\Comparison\Le;
use FKSDB\Models\Expressions\Comparison\Leq;
use FKSDB\Models\Expressions\Logic\LogicAnd;
use FKSDB\Models\Expressions\Logic\LogicOr;
use FKSDB\Models\Expressions\Logic\Not;
use FKSDB\Models\Expressions\Predicates\After;
use FKSDB\Models\Expressions\Predicates\Before;
use Nette\DI\Definitions\Statement;

class Helpers
{

    /** @var string[] */
    private const SEMANTIC_MAP = [
        'and' => LogicAnd::class,
        'or' => LogicOr::class,
        'neg' => Not::class,
        'after' => After::class,
        'before' => Before::class,
        'le' => Le::class,
        'leq' => Leq::class,
    ];

    /**
     * @param mixed $expression
     * @return array|mixed|void
     */
    public static function resolveMixedExpression($expression, array $semantic)
    {
        if ($expression instanceof Statement) {
            self::resolveStatementExpression($expression, $semantic);
        } elseif (is_iterable($expression)) {
            return self::resolveArrayExpression($expression, $semantic);
        } else {
            return $expression;
        }
    }

    public static function resolveStatementExpression(Statement $statement, array $semantic): Statement
    {
        $map = self::SEMANTIC_MAP + $semantic;
        $arguments = [];
        foreach ($statement->arguments as $attribute) {
            $arguments[] = self::resolveMixedExpression($attribute, $semantic);
        }
        $class = $statement->entity;
        if (!is_array($statement->entity)) {
            $class = $map[$statement->entity] ?? $class;
            if (function_exists($class)) { // workaround for Nette interpretation of entities
                $class = ['', $class];
            }
        }
        return new Statement($class, $arguments);
    }

    public static function resolveArrayExpression(iterable $expressionArray, array $semantic): array
    {
        $result = [];
        foreach ($expressionArray as $key => $expression) {
            $result[$key] = self::resolveMixedExpression($expression, $semantic);
        }
        return $result;
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
