<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking\Components;

use FKSDB\Components\Controls\Stalking\BaseStalkingComponent;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\PersonModel;

class RoleComponent extends BaseStalkingComponent
{

    final public function render(PersonModel $person, int $userPermissions): void
    {
        $this->beforeRender($person, _('Roles'), $userPermissions, FieldLevelPermission::ALLOW_RESTRICT);
        $login = $person->getLogin();
        $this->template->roles = $login ? $login->createGrantModels() : [];
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.role.latte');
    }
}
