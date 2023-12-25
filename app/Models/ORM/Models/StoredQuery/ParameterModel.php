<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\StoredQuery;

use Fykosak\NetteORM\Model\Model;
use Nette\InvalidStateException;

/**
 * @property-read int $parameter_id
 * @property-read int $query_id
 * @property-read QueryModel $query
 * @property-read string $name
 * @property-read string|null $description
 * @property-read ParameterType $type
 * @property-read int|null $default_integer
 * @property-read string|null $default_string
 */
final class ParameterModel extends Model
{
    /**
     * @return int|string
     */
    public function getDefaultValue()
    {
        switch ($this->type->value) {
            case ParameterType::INT:
            case ParameterType::BOOL:
                return $this->default_integer;
            case ParameterType::STRING:
                return $this->default_string;
            default:
                throw new InvalidStateException("Unsupported parameter type '$this->type->value'.");
        }
    }

    /**
     * @param int|string|bool $value
     * @phpstan-return array{
     *     default_integer?:int,
     *     default_string?:string,
     * }
     */
    public static function setInferDefaultValue(string $type, $value): array
    {
        $data = [];
        switch ($type) {
            case ParameterType::INT:
            case ParameterType::BOOL:
                $data['default_integer'] = (int)$value;
                break;
            case ParameterType::STRING:
                $data['default_string'] = (string)$value;
                break;
            default:
                throw new InvalidStateException("Unsupported parameter type '$type'.");
        }
        return $data;
    }

    /**
     * @param string $key
     * @return ParameterType|mixed
     * @throws \ReflectionException
     */
    public function &__get(string $key) // phpcs:ignore
    {
        $value = parent::__get($key);
        switch ($key) {
            case 'type':
                $value = ParameterType::tryFrom($value);
                break;
        }
        return $value;
    }
}
