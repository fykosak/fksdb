<?php

namespace FKSDB\Components\Controls\Helpers\Badges;

use Nette\Application\UI\Control;
use Nette\Templating\FileTemplate;

/**
 * Class ContestBadge
 * @package FKSDB\Components\Controls\Stalking\Helpers
 * @property FileTemplate $template
 */
class ContestBadge extends Control {

    /**
     * @param $contestId
     */
    public function render($contestId) {
        $this->template->contestId = $contestId;
        $this->template->setFile(__DIR__ . '/Contest.latte');
        $this->template->render();
    }
}

