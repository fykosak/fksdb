<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM;

class FieldLevelPermission
{
    public function __construct(
        public readonly FieldLevelPermissionValue $read,
        public readonly FieldLevelPermissionValue $write
    ) {
    }
}
