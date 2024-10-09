<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles;

use Fykosak\NetteORM\Model\Model;

interface ImplicitRole
{
    public function getModel(): Model;
}
