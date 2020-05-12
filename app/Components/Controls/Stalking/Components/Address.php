<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\ORM\Models\ModelPerson;

/**
 * Class Address
 * @package FKSDB\Components\Controls\Stalking
 */
class Address extends AbstractStalkingComponent {
    /**
     * @param ModelPerson $person
     * @param int $userPermissions
     */
    public function render(ModelPerson $person, int $userPermissions) {
        $this->beforeRender($person, $userPermissions);
        $this->template->MAddress = $person->getMPostContacts();
        $this->template->setFile(__DIR__ . '/Address.latte');
        $this->template->render();
    }

    /**
     * @return string
     */
    protected function getHeadline(): string {
        return _('Address');
    }

    /**
     * @return string[]
     */
    protected function getAllowedPermissions(): array {
        return [AbstractStalkingComponent::PERMISSION_FULL, AbstractStalkingComponent::PERMISSION_RESTRICT];
    }
}
