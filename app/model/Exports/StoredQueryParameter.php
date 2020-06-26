<?php

namespace FKSDB\StoredQuery;

use FKSDB\ORM\Models\StoredQuery\ModelStoredQueryParameter;

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
        $this->defaultValue = $defaultValue;
        $this->PDOType = $PDOType;
    }

    public function getDefaultValue() {
        return $this->defaultValue;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getPDOType(): int {
        return $this->PDOType;
    }
}
