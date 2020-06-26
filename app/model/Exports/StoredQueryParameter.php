<?php

namespace FKSDB\StoredQuery;

use Nette\InvalidStateException;

class StoredQueryParameter {
    /** @var mixed */
    private $defaultValue;
    /** @var string */
    private $name;
    /** @var int */
    private $PDOType;

    /**
     * StoredQueryParameter constructor.
     * @param string $name
     * @param $defaultValue
     * @param int $PDOType
     */
    public function __construct(string $name, $defaultValue, int $PDOType) {
        $this->name = $name;
        $this->defaultValue = self::getTypedValue($defaultValue, $PDOType);
        $this->PDOType = $PDOType;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue() {
        return $this->defaultValue;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getPDOType(): int {
        return $this->PDOType;
    }

    /**
     * @param mixed $value
     * @param int $type
     * @return bool|int|string
     */
    public function getTypedValue($value, int $type) {
        switch ($type) {
            case \PDO::PARAM_BOOL:
                return (bool)$value;
            case \PDO::PARAM_INT:
                return (int)$value;
            case \PDO::PARAM_STR:
                return (string)$value;
            default:
                throw new InvalidStateException("Unsupported parameter type '{$type}'.");
        }
    }
}
