<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\FormComponent;

use FKSDB\Components\Controls\FormControl\FormControl;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

abstract class FormComponent extends BaseComponent
{
    /**
     * @phpstan-param array<string,mixed> $params
     */
    public function render(array $params = []): void
    {
        $this->template->render($this->getTemplatePath(), $params);
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
        $this->appendSubmitButton($control->getForm())
            ->onClick[] = fn(SubmitButton $button) => $this->handleSuccess($button->getForm());
        return $control;
    }

    abstract protected function handleSuccess(Form $form): void;

    abstract protected function appendSubmitButton(Form $form): SubmitButton;

    abstract protected function configureForm(Form $form): void;
}
