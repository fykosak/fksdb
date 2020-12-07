<?php

namespace FKSDB\Components\Controls\FormControl;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Model\Exceptions\BadTypeException;
use Nette\Application\UI\Form;

/**
 * Bootstrap compatible form control with support for AJAX in terms
 * of form/container groups.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class FormControl extends BaseComponent {

    public const SNIPPET_MAIN = 'groupContainer';

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

    public function render(): void {
        if (!isset($this->template->mainContainer)) {
            $this->template->mainContainer = $this->getComponent('form');
        }
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.containers.latte');
        $this->template->render();
    }
}
