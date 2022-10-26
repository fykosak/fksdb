<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking\Components;

use FKSDB\Components\Controls\Stalking\BaseStalkingComponent;
use FKSDB\Models\ORM\FieldLevelPermission;

class FlagComponent extends BaseStalkingComponent
{
    final public function render(): void
    {
        $this->beforeRender();
        $this->template->flags = $this->person->getFlags();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.flag.latte');
    }

    protected function getMinimalPermissions(): int
    {
        return FieldLevelPermission::ALLOW_RESTRICT;
    }
}
