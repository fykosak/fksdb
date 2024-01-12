<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Models\ORM\FieldLevelPermission;

class RoleComponent extends BaseComponent
{

    final public function render(): void
    {
        if ($this->beforeRender()) {
            $login = $this->person->getLogin();
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'role.latte', [
                'roles' => $login ? $login->createContestRoles() : [],
            ]);
        }
    }

    protected function getMinimalPermissions(): int
    {
        return FieldLevelPermission::ALLOW_FULL;
    }
}
