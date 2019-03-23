<?php

namespace FKSDB\Components\Controls\FormControl;

use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;
use Nette\Templating\FileTemplate;

/**
 * Bootstrap compatible form control with support for AJAX in terms
 * of form/container groups.
 *
 * @author Michal Koutný <michal@fykos.cz>
 * @property FileTemplate $template
 */
class FormControl extends Control {

    const SNIPPET_MAIN = 'groupContainer';

    const TEMPLATE_PATH = 'FormControl.containers.latte';

    /**
     * FormControl constructor.
     * @param IContainer|NULL $parent
     * @param null $name
     */
    public function __construct(IContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);

        $form = $this->createForm();
        $this->addComponent($form, 'form');
    }

    /**
     * @return Form
     */
    protected function createForm(): Form {
        return new Form();
    }

    /**
     * @return Form
     * @throws BadRequestException
     */
    public final function getForm(): Form {
        $component = $this->getComponent('form');
        if (!$component instanceof Form) {
            throw new BadRequestException();
        }
        return $component;
    }

    /**
     * @return string
     */
    private function getTemplateFile() {
        return __DIR__ . DIRECTORY_SEPARATOR . self::TEMPLATE_PATH;
    }

    public function render() {
        if (!isset($this->template->mainContainer)) {
            $this->template->mainContainer = $this->getComponent('form');
        }
        $this->template->setFile($this->getTemplateFile());
        $this->template->render();
    }

}
