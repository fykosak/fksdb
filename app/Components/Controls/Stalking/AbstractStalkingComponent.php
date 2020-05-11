<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\ORM\Models\ModelPerson;
/**
 * Class StalkingComponent
 * @package FKSDB\Components\Controls\Stalking
 *
 */
abstract class AbstractStalkingComponent extends StalkingControl {
    /**
     * @param ModelPerson $person
     * @param int $userPermissions
     */
    public function beforeRender(ModelPerson $person, int $userPermissions) {
        parent::beforeRender($person, $userPermissions);
        $this->template->headline = $this->getHeadline();
        $this->template->minimalPermissions = min($this->getAllowedPermissions());
    }

    /**
     * @return string
     */
    abstract protected function getHeadline(): string;

    /**
     * @return string[]
     */
    abstract protected function getAllowedPermissions(): array;
}
