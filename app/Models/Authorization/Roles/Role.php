<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles;

use Nette\Utils\Html;

interface Role extends \Nette\Security\Role
{
    public function badge(): Html;
}
