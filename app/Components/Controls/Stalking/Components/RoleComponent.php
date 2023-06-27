<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking\Components;

use FKSDB\Components\Controls\Stalking\BaseStalkingComponent;
use FKSDB\Models\ORM\FieldLevelPermission;

class RoleComponent extends BaseStalkingComponent
{

    final public function render(): void
    {
        if ($this->beforeRender()) {
            $login = $this->person->getLogin();
            $this->template->roles = $login ? $login->createGrantModels() : [];
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.role.latte');
        }
    }

    protected function getMinimalPermissions(): int
    {
        return FieldLevelPermission::ALLOW_FULL;
    }
}
