<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Person\Detail;

use FKSDB\Models\ORM\FieldLevelPermissionValue;

class OrgListComponent extends BaseComponent
{
    final public function render(): void
    {
        if ($this->beforeRender()) {
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'org.latte');
        }
    }

    protected function getMinimalPermissions(): FieldLevelPermissionValue
    {
        return FieldLevelPermissionValue::Restrict;
    }
}
