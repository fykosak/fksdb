<?php

namespace FKSDB\Components\DatabaseReflection;

class FieldLevelPermission {
    /**
     * @var int
     */
    public $read;
    /**
     * @var int
     */
    public $write;

    /**
     * FieldLevelPermission constructor.
     * @param int $read
     * @param int $write
     */
    public function __construct(int $read, int $write) {
        $this->read = $read;
        $this->write = $write;
    }
}
