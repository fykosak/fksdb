<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\ORM\Models\ModelPerson;

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
