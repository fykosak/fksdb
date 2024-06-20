<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM;

enum FieldLevelPermissionValue: int
{
    case NoAccess = 1;
    case Basic = 16;
    case Restrict = 128;
    case Full = 1024;
}
