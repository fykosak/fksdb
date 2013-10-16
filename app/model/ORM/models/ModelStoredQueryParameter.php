<?php

use Nette\InvalidStateException;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 */
class ModelStoredQueryParameter extends AbstractModelSingle {

    const TYPE_INT = 'integer';
    const TYPE_STR = 'string';

    public function getDefaultValue() {
        switch ($this->type) {
            case self::TYPE_INT:
                return $this->default_integer;
            case self::TYPE_STR:
                return $this->default_string;
            default:
                throw new InvalidStateException("Unsupported parameter type '{$this->type}'.");
        }
    }

    public function setDefaultValue($value) {
        switch ($this->type) {
            case self::TYPE_INT:
                $this->default_integer = $value;
                break;
            case self::TYPE_STR:
                $this->default_string = $value;
                break;
            default:
                throw new InvalidStateException("Unsupported parameter type '{$this->type}'.");
        }
    }

    public function getPDOType() {
        switch ($this->type) {
            case self::TYPE_INT:
                return PDO::PARAM_INT;
            case self::TYPE_STR:
                return PDO::PARAM_STR;
            default:
                throw new InvalidStateException("Unsupported parameter type '{$this->type}'.");
        }
    }

}
