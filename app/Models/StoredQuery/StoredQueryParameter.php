<?php

declare(strict_types=1);

namespace FKSDB\Models\StoredQuery;

use FKSDB\Models\ORM\Models\StoredQuery\ParameterModel;
use FKSDB\Models\ORM\Models\StoredQuery\ParameterType;
use Nette\InvalidStateException;

class StoredQueryParameter
{
    /** @var mixed */
    public $defaultValue;
    public string $name;
    public ParameterType $type;
    public ?string $description;

    public static function fromModel(ParameterModel $model): self
    {
        return new StoredQueryParameter(
            $model->name,
            $model->getDefaultValue(),
            $model->type,
            $model->description
        );
    }

    /**
     * StoredQueryParameter constructor.
     * @param mixed $defaultValue
     */
    public function __construct(string $name, $defaultValue, ParameterType $type, ?string $description = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->description = $description;
        self::setTypedValue($defaultValue);
    }

    public function getPDOType(): int
    {
        return $this->type->getPDOType();
    }

    /**
     * @param mixed $value
     */
    private function setTypedValue($value): void
    {
        switch ($this->type->value) {
            case ParameterType::BOOL:
                $this->defaultValue = (bool)$value;
                break;
            case ParameterType::INT:
                $this->defaultValue = (int)$value;
                break;
            case ParameterType::STRING:
                $this->defaultValue = (string)$value;
                break;
            default:
                throw new InvalidStateException("Unsupported parameter type '$this->type->value'.");
        }
    }
}
