<?php

namespace FKSDB\Components\Controls\Stalking\Components;

use FKSDB\Components\Controls\Stalking\StalkingControl;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\ModelPerson;

/**
 * Class Flag
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Flag extends StalkingControl {

    public function render(ModelPerson $person, int $userPermissions): void {
        $this->beforeRender($person, _('Flags'), $userPermissions, FieldLevelPermission::ALLOW_RESTRICT);
        $this->template->flags = $person->getFlags();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.flag.latte');
        $this->template->render();
    }
}
