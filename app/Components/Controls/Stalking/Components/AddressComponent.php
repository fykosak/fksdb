<?php

namespace FKSDB\Components\Controls\Stalking\Components;

use FKSDB\Components\Controls\Stalking\BaseStalkingComponent;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\ModelPerson;

class AddressComponent extends BaseStalkingComponent {

    final public function render(ModelPerson $person, int $userPermissions): void {
        $this->beforeRender($person, _('Addresses'), $userPermissions, FieldLevelPermission::ALLOW_RESTRICT);
        $this->template->postContacts = $person->getPostContacts();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.address.latte');
    }
}
