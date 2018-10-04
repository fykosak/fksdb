<?php

namespace FKSDB\Components\Controls\Stalking\Helpers;

use Nette\Application\UI\Control;
use Nette\Templating\FileTemplate;

/**
 * Class ContestBadge
 * @package FKSDB\Components\Controls\Stalking\Helpers
 * @property FileTemplate $template
 */
class PermissionDenied extends Control {

    public function render() {
        $this->template->setFile(__DIR__ . '/PermissionDenied.latte');
        $this->template->render();
    }
}

