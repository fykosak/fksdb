<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking\Components;

use FKSDB\Components\Controls\Stalking\BaseStalkingComponent;
use FKSDB\Models\ORM\FieldLevelPermissionValue;

class FlagComponent extends BaseStalkingComponent
{
    final public function render(): void
    {
        if ($this->beforeRender()) {
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'flag.latte');
        }
    }

    protected function getMinimalPermissions(): FieldLevelPermissionValue
    {
        return FieldLevelPermissionValue::Restrict;
    }
}
