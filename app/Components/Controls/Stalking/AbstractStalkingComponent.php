<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\ORM\Models\ModelPerson;

abstract class AbstractStalkingComponent extends StalkingControl {

    public function beforeRender(ModelPerson $person, int $userPermissions): void {
        parent::beforeRender($person, $userPermissions);
        $this->template->headline = $this->getHeadline();
        $this->template->minimalPermissions = min($this->getAllowedPermissions());
    }

    abstract protected function getHeadline(): string;

    abstract protected function getAllowedPermissions(): array;
}
