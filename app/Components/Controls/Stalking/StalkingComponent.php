<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\Controls\Stalking\Helpers\ContestBadge;
use Nette\Application\UI\Control;

abstract class StalkingComponent extends Control {

    public function createComponentContestBadge() {
        $control = new ContestBadge();
        return $control;
    }
}
