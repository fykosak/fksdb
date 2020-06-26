<?php

namespace FKSDB\StoredQuery;

use Nette\InvalidStateException;

/**
 * Class StoredQueryParameter
 * @author Michal Červeňák <miso@fykos.cz>
 */
class StoredQueryParameter {
    /** @var mixed */
    private $defaultValue;
    /** @var string */
    private $name;
    /** @var int */
    private $PDOType;
    /**
     * @var string|null
     */
    private $description;

    /**
     * StoredQueryParameter constructor.
     * @param string $name
     * @param mixed $defaultValue
     * @param int $PDOType
     * @param string|null $description
     */
    public function __construct(string $name, $defaultValue, int $PDOType, string $description = null) {
        $this->name = $name;
        $this->defaultValue = self::getTypedValue($defaultValue, $PDOType);
        $this->PDOType = $PDOType;
        $this->description = $description;
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
     * @return string|null
     */
    public function getDescription() {
        return $this->description;
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
