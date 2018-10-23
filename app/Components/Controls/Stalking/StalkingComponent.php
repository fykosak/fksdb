<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\Controls\Stalking\Helpers\ContestBadge;
use FKSDB\Components\Controls\Stalking\Helpers\PermissionDenied;
use FKSDB\ORM\ModelPerson;
use Nette\Application\UI\Control;
use Nette\Templating\FileTemplate;

/**
 * Class StalkingComponent
 * @package FKSDB\Components\Controls\Stalking
 * @property FileTemplate $template
 */
abstract class StalkingComponent extends Control {
    const PERMISSION_FULL = 'full';
    const PERMISSION_RESTRICT = 'restrict';
    const PERMISSION_BASIC = 'basic';
    /**
     * @var string
     */
    protected $mode;
    /**
     * @var ModelPerson;
     */
    protected $modelPerson;

    public function __construct(ModelPerson $modelPerson, $mode) {
        parent::__construct();
        $this->mode = $mode;
        $this->modelPerson = $modelPerson;
    }

    public function beforeRender() {
        $this->template->mode = $this->mode;
    }

    public function createComponentContestBadge() {
        $control = new ContestBadge();
        return $control;
    }

    public function createComponentPermissionDenied() {
        $control = new PermissionDenied();
        return $control;
    }
}
