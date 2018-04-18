<?php

namespace FKSDB\Components\Controls\Stalking\Helpers;

use Nette\Application\UI\Control;

class ContestBadge extends Control {

    public function render($contestId) {
        $template = $this->template;
        $template->contestId = $contestId;
        $template->setFile(__DIR__ . '/ContestBadge.latte');
        $template->render();
    }
}

