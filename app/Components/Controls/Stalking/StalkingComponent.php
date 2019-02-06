<?php

namespace FKSDB\Components\Controls\Stalking;

use FKSDB\Components\Controls\Stalking\Helpers\ContestBadge;
use FKSDB\Components\Controls\Stalking\Helpers\PermissionDenied;
use FKSDB\ORM\ModelPerson;
use Nette\Application\UI\Control;
use Nette\Localization\ITranslator;
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
    /**
     * @var ITranslator
     */
    protected $translator;

    /**
     * StalkingComponent constructor.
     * @param ModelPerson $modelPerson
     * @param ITranslator $translator
     * @param $mode
     */
    public function __construct(ModelPerson $modelPerson, ITranslator $translator, $mode) {
        parent::__construct();
        $this->mode = $mode;
        $this->modelPerson = $modelPerson;
        $this->translator = $translator;
    }

    public function beforeRender() {
        $this->template->setTranslator($this->translator);
        $this->template->mode = $this->mode;
    }

    /**
     * @return ContestBadge
     */
    public function createComponentContestBadge() {
        $control = new ContestBadge();
        return $control;
    }

    /**
     * @return PermissionDenied
     */
    public function createComponentPermissionDenied() {
        $control = new PermissionDenied();
        return $control;
    }
}
