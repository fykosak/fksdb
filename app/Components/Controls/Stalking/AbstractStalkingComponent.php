<?php

namespace FKSDB\Components\Controls\Stalking;

use Nette\Templating\FileTemplate;

/**
 * Class StalkingComponent
 * @package FKSDB\Components\Controls\Stalking
 * @property FileTemplate $template
 */
abstract class AbstractStalkingComponent extends StalkingControl {

    public function beforeRender() {
        parent::beforeRender();
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
