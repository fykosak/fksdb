<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\DatabaseReflection\FieldLevelPermission;
use FKSDB\ORM\Models\ModelPerson;

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
        $this->beforeRender($person, $userPermissions);
        $this->template->MAddress = $person->getMPostContacts();
        $this->template->setFile(__DIR__ . '/Address.latte');
        $this->template->render();
    }

    protected function getHeadline(): string {
        return _('Address');
    }

    protected function getMinimalPermissions(): int {
        return FieldLevelPermission::ALLOW_RESTRICT;
    }
}
