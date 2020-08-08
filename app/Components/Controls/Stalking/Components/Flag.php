<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\DBReflection\FieldLevelPermission;
use FKSDB\ORM\Models\ModelPerson;

/**
 * Class Flag
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Flag extends AbstractStalkingComponent {
    /**
     * @param ModelPerson $person
     * @param int $userPermissions
     * @return void
     */
    public function render(ModelPerson $person, int $userPermissions) {
        $this->beforeRender($person, _('Flags'), $userPermissions, FieldLevelPermission::ALLOW_RESTRICT);
        $this->template->flags = $person->getMPersonHasFlags();
        $this->template->setFile(__DIR__ . '/Flag.latte');
        $this->template->render();
    }
}
