<?php

namespace FKSDB\Components\Controls\FormControl;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Exceptions\BadTypeException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;

/**
 * Bootstrap compatible form control with support for AJAX in terms
 * of form/container groups.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class FormControl extends BaseComponent {

    public const SNIPPET_MAIN = 'groupContainer';

    public const TEMPLATE_PATH = 'FormControl.containers.latte';

    protected function createComponentForm(): Form {
        return new Form();
    }

    /**
     * @return Form
     * @throws BadRequestException
     */
    final public function getForm(): Form {
        $component = $this->getComponent('form');
        if (!$component instanceof Form) {
            throw new BadTypeException(Form::class, $component);
        }
        return $component;
    }

    private function getTemplateFile(): string {
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
