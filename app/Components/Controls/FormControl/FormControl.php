<?php

namespace FKSDB\Components\Controls\FormControl;

use FKSDB\Exceptions\BadTypeException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
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

    protected function createComponentForm(): Form {
        return new Form();
    }

    /**
     * @return Form
     * @throws BadTypeException
     */
    final public function getForm(): Form {
        $component = $this->getComponent('form');
        if (!$component instanceof Form) {
            throw new BadTypeException(Form::class, $component);
        }
        return $component;
    }


    public function render() {
        if (!isset($this->template->mainContainer)) {
            $this->template->mainContainer = $this->getComponent('form');
        }
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'FormControl.containers.latte');
        $this->template->render();
    }
}
