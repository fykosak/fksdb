<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking\Components;

use FKSDB\Components\Controls\Stalking\BaseStalkingComponent;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\PersonModel;

class FlagComponent extends BaseStalkingComponent
{
    final public function render(PersonModel $person, int $userPermissions): void
    {
        $this->beforeRender($person, _('Flags'), $userPermissions, FieldLevelPermission::ALLOW_RESTRICT);
        $this->getTemplate()->flags = $person->getFlags();
        $this->getTemplate()->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.flag.latte');
    }
}
