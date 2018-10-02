<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\Controls\Stalking\Helpers\ContestBadge;
use FKSDB\ORM\ModelPerson;
use Nette\Application\UI\Control;
use Nette\Templating\FileTemplate;

/**
 * Class StalkingComponent
 * @package FKSDB\Components\Controls\Stalking
 * @property FileTemplate $template
 */
abstract class StalkingComponent extends Control {
    /**
     * @var string
     */
    protected $mode;
    /**
     * @var ModelPerson;
     */
    protected $modelPerson;

    public function __construct(ModelPerson $modelPerson, $mode = null) {
        parent::__construct();
        $this->mode = $mode;
        $this->modelPerson = $modelPerson;
    }

    public function createComponentContestBadge() {
        $control = new ContestBadge();
        return $control;
    }
}
