<?php

namespace FKSDB\ORM\Models\StoredQuery;

use FKSDB\ORM\AbstractModelSingle;
use Nette\InvalidStateException;
use PDO;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read string type
 * @property-read int default_integer
 * @property-read string default_string
 * @property-read int query_id
 */
class ModelStoredQueryParameter extends AbstractModelSingle {

    public const TYPE_INT = 'integer';
    public const TYPE_STRING = 'string';
    public const TYPE_BOOL = 'bool';

    /**
     * @return int|string
     * @throws InvalidStateException
     */
    public function getDefaultValue() {
        switch ($this->type) {
            case self::TYPE_INT:
            case self::TYPE_BOOL:
                return $this->default_integer;
            case self::TYPE_STRING:
                return $this->default_string;
            default:
                throw new InvalidStateException("Unsupported parameter type '{$this->type}'.");
        }
    }

    /**
     * @param mixed $value
     * @throws InvalidStateException
     */
    public function setDefaultValue($value): void {
        switch ($this->type) {
            case self::TYPE_INT:
            case self::TYPE_BOOL:
                $this->default_integer = (int)$value;
                break;
            case self::TYPE_STRING:
                $this->default_string = $value;
                break;
            default:
                throw new InvalidStateException("Unsupported parameter type '{$this->type}'.");
        }
    }

    /**
     * @return int
     * @throws InvalidStateException
     */
    public function getPDOType() {
        switch ($this->type) {
            case self::TYPE_INT:
            case self::TYPE_BOOL:
                return PDO::PARAM_INT;
            case self::TYPE_STRING:
                return PDO::PARAM_STR;
            default:
                throw new InvalidStateException("Unsupported parameter type '{$this->type}'.");
        }
    }

}
