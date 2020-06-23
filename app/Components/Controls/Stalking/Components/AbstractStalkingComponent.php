<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\ORM\Models\ModelPerson;

/**
 * Class AbstractStalkingComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class AbstractStalkingComponent extends StalkingControl {
    /**
     * @param ModelPerson $person
     * @param int $userPermissions
     * @return void
     */
    public function beforeRender(ModelPerson $person, int $userPermissions) {
        parent::beforeRender($person, $userPermissions);
        $this->template->headline = $this->getHeadline();
        $this->template->minimalPermissions = $this->getMinimalPermissions();
    }

    abstract protected function getHeadline(): string;

    abstract protected function getMinimalPermissions(): int;
}
