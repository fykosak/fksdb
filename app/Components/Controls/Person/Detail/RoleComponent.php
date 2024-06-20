<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Models\ORM\FieldLevelPermissionValue;

class RoleComponent extends BaseComponent
{

    final public function render(): void
    {
        if ($this->beforeRender()) {
            $login = $this->person->getLogin();
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'role.latte', [
                'roles' => $login ? $login->getExplicitContestRoles() : [],
            ]);
        }
    }

    protected function getMinimalPermissions(): FieldLevelPermissionValue
    {
        return FieldLevelPermissionValue::Full;
    }
}
