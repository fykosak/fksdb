<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking\Components;

use FKSDB\Components\Controls\Stalking\BaseStalkingComponent;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\ModelPerson;

class FlagComponent extends BaseStalkingComponent {

    final public function render(ModelPerson $person, int $userPermissions): void {
        $this->beforeRender($person, _('Flags'), $userPermissions, FieldLevelPermission::ALLOW_RESTRICT);
        $this->template->flags = $person->getFlags();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.flag.latte');
    }
}
