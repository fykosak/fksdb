<?php

namespace FKSDB\Components\Controls\Stalking\Components;

use FKSDB\Components\Controls\Stalking\BaseStalkingComponent;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\ModelPerson;

/**
 * Class Address
 * @author Michal Červeňák <miso@fykos.cz>
 */
class AddressComponent extends BaseStalkingComponent {

    public function render(ModelPerson $person, int $userPermissions): void {
        $this->beforeRender($person, _('Addresses'), $userPermissions, FieldLevelPermission::ALLOW_RESTRICT);
        $this->template->postContacts = $person->getPostContacts();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.address.latte');
        $this->template->render();
    }
}
