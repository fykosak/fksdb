<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\StoredQuery;

use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Utils\FakeStringEnum;
use Nette\InvalidStateException;
use Nette\Utils\Html;

final class ParameterType extends FakeStringEnum implements EnumColumn
{
    public const INT = 'integer';
    public const STRING = 'string';
    public const BOOL = 'bool';

    public function badge(): Html
    {
        return Html::el('span');
    }

    public function label(): string
    {
        return '';
    }

    public static function cases(): array
    {
        return [
            new self(self::BOOL),
            new self(self::STRING),
            new self(self::INT),
        ];
    }

    public function getPDOType(): int
    {
        switch ($this->value) {
            case self::INT:
                return \PDO::PARAM_INT;
            case self::BOOL:
                return \PDO::PARAM_BOOL;
            case self::STRING:
                return \PDO::PARAM_STR;
            default:
                throw new InvalidStateException("Unsupported parameter type '$this->value'.");
        }
    }
}
