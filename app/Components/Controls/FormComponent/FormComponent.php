<?php

namespace FKSDB\Components\Controls\FormComponent;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Exceptions\BadTypeException;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

abstract class FormComponent extends BaseComponent {

    public function render(): void {
        $this->template->render($this->getTemplatePath());
    }

    protected function getTemplatePath(): string {
        return __DIR__ . DIRECTORY_SEPARATOR . 'layout.latte';
    }

    protected function createFormControl(): FormControl {
        return new FormControl($this->getContext());
    }

    /**
     * @throws BadTypeException
     */
    final protected function getForm(): Form {
        $control = $this->getComponent('formControl');
        if (!$control instanceof FormControl) {
            throw new BadTypeException(FormControl::class, $control);
        }
        return $control->getForm();
    }

    /**
     * @throws BadTypeException
     */
    final protected function createComponentFormControl(): FormControl {
        $control = $this->createFormControl();
        $this->configureForm($control->getForm());
        $this->appendSubmitButton($control->getForm())
            ->onClick[] = function (SubmitButton $button) {
            $this->handleSuccess($button);
        };
        return $control;
    }

    abstract protected function handleSuccess(SubmitButton $button): void;

    abstract protected function appendSubmitButton(Form $form): SubmitButton;

    abstract protected function configureForm(Form $form): void;
}
