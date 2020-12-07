<?php

namespace FKSDB\Model\DBReflection;
/**
 * Class FieldLevelPermission
 * @author Michal Červeňák <miso@fykos.cz>
 */
class FieldLevelPermission {

    public const ALLOW_ANYBODY = 1;
    public const ALLOW_BASIC = 16;
    public const ALLOW_RESTRICT = 128;
    public const ALLOW_FULL = 1024;

    public int $read;

    public int $write;

    public function __construct(int $read, int $write) {
        $this->read = $read;
        $this->write = $write;
    }
}
