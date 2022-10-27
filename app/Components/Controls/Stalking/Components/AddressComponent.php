<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Stalking\Components;

use FKSDB\Components\Controls\Stalking\BaseStalkingComponent;
use FKSDB\Models\ORM\FieldLevelPermission;

class AddressComponent extends BaseStalkingComponent
{
    final public function render(): void
    {
        if ($this->beforeRender()) {
            $this->template->postContacts = $this->person->getPostContacts();
            $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.address.latte');
        }
    }

    protected function getMinimalPermissions(): int
    {
        return FieldLevelPermission::ALLOW_RESTRICT;
    }
}
