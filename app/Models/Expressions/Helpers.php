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
use Nette\Schema\Elements\AnyOf;
use Nette\Schema\Expect;

class Helpers
{
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
    public static function resolveMixedExpression($expression)
    {
        if ($expression instanceof Statement) {
            return self::resolveStatementExpression($expression);
        } elseif (is_iterable($expression)) {
            return self::resolveArrayExpression($expression);
        } else {
            return $expression;
        }
    }

    public static function resolveStatementExpression(Statement $statement): Statement
    {
        $arguments = [];
        foreach ($statement->arguments as $attribute) {
            $arguments[] = self::resolveMixedExpression($attribute);
        }
        $class = $statement->entity;
        if (!is_array($statement->entity)) {
            $class = self::SEMANTIC_MAP[$statement->entity] ?? $class;//@phpstan-ignore-line
            if (function_exists($class)) { //@phpstan-ignore-line // workaround for Nette interpretation of entities
                $class = ['', $class];
            }
        }
        return new Statement($class, $arguments);
    }

    /**
     * @param iterable<string|int,mixed> $expressionArray
     * @return array<string|int,mixed>
     */
    public static function resolveArrayExpression(iterable $expressionArray): array
    {
        $result = [];
        foreach ($expressionArray as $key => $expression) {
            $result[$key] = self::resolveMixedExpression($expression);
        }
        return $result;
    }

    public static function createExpressionSchemaType(): AnyOf
    {
        return Expect::anyOf(Expect::string(), Expect::type(Statement::class))->before(
            fn($value) => self::resolveMixedExpression($value)
        );
    }

    public static function createBoolExpressionSchemaType(bool $default): AnyOf
    {
        return Expect::anyOf(Expect::bool($default), Expect::type(Statement::class))->before(
            fn($value) => self::resolveMixedExpression($value)
        );
    }
}
