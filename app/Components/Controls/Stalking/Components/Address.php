<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\ORM\Models\ModelPerson;

/**
 * Class Address
 * *
 */
class Address extends AbstractStalkingComponent {
    /**
     * @param ModelPerson $person
     * @param int $userPermissions
     * @return void
     */
    public function render(ModelPerson $person, int $userPermissions) {
        $this->beforeRender($person, $userPermissions);
        $this->template->MAddress = $person->getMPostContacts();
        $this->template->setFile(__DIR__ . '/Address.latte');
        $this->template->render();
    }

    protected function getHeadline(): string {
        return _('Address');
    }

    /**
     * @return string[]
     */
    protected function getAllowedPermissions(): array {
        return [self::PERMISSION_FULL, self::PERMISSION_RESTRICT];
    }
}
