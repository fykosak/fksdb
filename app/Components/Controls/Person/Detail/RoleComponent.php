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
            $this->template->roles = $login ? $login->createGrantModels() : [];
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'role.latte');
        }
    }

    protected function getMinimalPermissions(): FieldLevelPermissionValue
    {
        return FieldLevelPermissionValue::Full;
    }
}
