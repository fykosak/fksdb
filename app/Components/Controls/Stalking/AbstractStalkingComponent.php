<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\ORM\Models\ModelPerson;
use Nette\Templating\FileTemplate;

/**
 * Class StalkingComponent
 * @package FKSDB\Components\Controls\Stalking
 * @property FileTemplate $template
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
