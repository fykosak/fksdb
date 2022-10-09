<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking\Components;

use FKSDB\Components\Controls\Stalking\BaseStalkingComponent;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\PersonModel;

class AddressComponent extends BaseStalkingComponent
{
    final public function render(PersonModel $person, int $userPermissions): void
    {
        $this->beforeRender($person, _('Addresses'), $userPermissions, FieldLevelPermission::ALLOW_RESTRICT);
        $this->getTemplate()->postContacts = $person->getPostContacts();
        $this->getTemplate()->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.address.latte');
    }
}
