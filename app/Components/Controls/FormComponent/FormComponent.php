<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\FormComponent;

use FKSDB\Components\Controls\FormControl\FormControl;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

abstract class FormComponent extends BaseComponent
{

    public function render(): void
    {
        $this->template->render($this->getTemplatePath());
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'layout.latte';
    }

    protected function createFormControl(): FormControl
    {
        return new FormControl($this->getContext());
    }

    final protected function getForm(): Form
    {
        /** @var FormControl $control */
        $control = $this->getComponent('formControl');
        return $control->getForm();
    }

    final protected function createComponentFormControl(): FormControl
    {
        $control = $this->createFormControl();
        $this->configureForm($control->getForm());
        $this->appendSubmitButton($control->getForm());
        $control->getForm()->onSuccess[] = fn(Form $form) => $this->handleSuccess($form);
        return $control;
    }

    abstract protected function handleSuccess(Form $form): void;

    abstract protected function appendSubmitButton(Form $form): void;

    abstract protected function configureForm(Form $form): void;
}
