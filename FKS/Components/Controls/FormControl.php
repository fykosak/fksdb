<?php

namespace FKS\Components\Controls;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;

/**
 * Bootstrap compatible form control with support for AJAX in terms
 * of form/container groups.
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class FormControl extends Control {

    const BOOTSTRAP_GRID = 12;
    const GROUP_CONTAINER = 'container';
    const GROUP_CONTROL_GROUP = 'group';
    const SNIPPET_MAIN = 'groupContainer';

    /* Parameters for rendering form layout. */

    public $gridLabels = 3;
    public $gridFields = 6;
    public $gridButton = 4;
    public $gridSubfieldset = 8;
    private static $templates = array(
        self::GROUP_CONTAINER => 'FormControl.containers.latte',
        self::GROUP_CONTROL_GROUP => 'FormControl.groups.latte',
    );
    private $groupMode = self::GROUP_CONTROL_GROUP;

    public function __construct(IContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        $form = new Form();
        $this->addComponent($form, 'form');
    }

    public function getGroupMode() {
        return $this->groupMode;
    }

    public function setGroupMode($groupMode) {
        $this->groupMode = $groupMode;
    }

    private function getTemplateFile() {
        return __DIR__ . DIRECTORY_SEPARATOR . self::$templates[$this->groupMode];
    }

    public function render() {
        $this->template->fullLeft = $this->gridLabels;
        $this->template->fullRight = $this->gridFields;
        $this->template->btnWidth = $this->gridButton;
        $this->template->subWidth = $this->gridSubfieldset;
        $this->template->subLeft = ceil(self::BOOTSTRAP_GRID * ($this->gridSubfieldset - $this->gridFields) / $this->gridSubfieldset);
        $this->template->subRight = self::BOOTSTRAP_GRID - $this->template->subLeft;
        $this->template->subOffset = $this->gridLabels + $this->gridFields - $this->gridSubfieldset;


        if (!isset($this->template->mainContainer)) {
            $this->template->mainContainer = $this->getComponent('form');
        }

        $this->template->setFile($this->getTemplateFile());
        $this->template->render();
    }

}
