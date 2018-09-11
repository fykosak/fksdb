<?php

namespace FKSDB\Components\Controls\FormControl;

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

    const SNIPPET_MAIN = 'groupContainer';

    const templatePath = 'FormControl.containers.latte';

    public function __construct(IContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        $form = new Form();
        $this->addComponent($form, 'form');
    }

    /**
     * @return Form
     */
    public final function getForm() {
        return $this->getComponent('form');
    }

    private function getTemplateFile() {
        return __DIR__ . DIRECTORY_SEPARATOR . self::templatePath;
    }

    public function render() {
        if (!isset($this->template->mainContainer)) {
            $this->template->mainContainer = $this->getComponent('form');
        }
        $this->template->setFile($this->getTemplateFile());
        $this->template->render();
    }

}
