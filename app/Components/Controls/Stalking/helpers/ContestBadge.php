<?php

namespace FKSDB\Components\Controls\Stalking\Helpers;

use Nette\Application\UI\Control;
use Nette\Templating\FileTemplate;

/**
 * Class ContestBadge
 * @package FKSDB\Components\Controls\Stalking\Helpers
 * @property FileTemplate $template
 */
class ContestBadge extends Control {

    public function render($contestId) {
        $this->template->contestId = $contestId;
        $this->template->setFile(__DIR__ . '/ContestBadge.latte');
        $this->template->render();
    }
}

