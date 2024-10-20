<?php

declare(strict_types=1);

namespace FKSDB\Models\Authorization\Roles\Base;

use FKSDB\Models\Authorization\Roles\Role;
use Nette\Utils\Html;

final class GuestRole implements Role
{
    public const RoleId = 'guest'; //@phpcs:ignore

    public function badge(): Html
    {
        return Html::el('span')
            ->addAttributes(['class' => 'me-2 badge bg-primary '])
            ->addText('Guest');
    }

    public function getRoleId(): string
    {
        return self::RoleId;
    }
}
