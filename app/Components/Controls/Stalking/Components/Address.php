<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\ORM\Models\ModelPerson;
use Tracy\Debugger;

/**
 * Class Address
 * @author Michal Červeňák <miso@fykos.cz>
 */
class Address extends AbstractStalkingComponent {
    /**
     * @param ModelPerson $person
     * @param int $userPermissions
     * @return void
     */
    public function render(ModelPerson $person, int $userPermissions) {
        $this->beforeRender($person, _('Address'), $userPermissions, FieldLevelPermission::ALLOW_RESTRICT);
        $this->template->MAddress = $person->getMPostContacts();
        $this->template->setFile(__DIR__ . '/address.latte');
        $this->template->render();
    }
}
