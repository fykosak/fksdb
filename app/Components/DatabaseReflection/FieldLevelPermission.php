<?php

namespace FKSDB\Components\DatabaseReflection;
/**
 * Class FieldLevelPermission
 * @author Michal Červeňák <miso@fykos.cz>
 */
class FieldLevelPermission {

    const ALLOW_ANYBODY = 1;
    const ALLOW_BASIC = 16;
    const ALLOW_RESTRICT = 128;
    const ALLOW_FULL = 1024;
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
