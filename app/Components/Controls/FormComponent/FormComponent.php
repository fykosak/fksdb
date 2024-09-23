<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\FormComponent;

use FKSDB\Components\Controls\FormControl\FormControl;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\Application\AbortException;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Tracy\Debugger;

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
        $this->appendSubmitButton($control->getForm())->onClick[] =
            function (SubmitButton $button): void {
                try {
                    $this->handleSuccess($button->getForm());
                } catch (AbortException $exception) {
                    throw $exception;
                } catch (\Throwable $exception) {
                    if (!$this->onException($exception)) {
                        Debugger::log($exception, Debugger::EXCEPTION);
                        Debugger::barDump($exception);
                        $this->flashMessage(_('Error in the form.'), Message::LVL_ERROR);
                    }
                }
            };
        return $control;
    }

    /**
     * @return bool
     * return true if exception is handled by method, otherwise handled by default handler
     */
    protected function onException(\Throwable $exception): bool
    {
        return false;
    }
    abstract protected function handleSuccess(Form $form): void;

    abstract protected function appendSubmitButton(Form $form): SubmitButton;

    abstract protected function configureForm(Form $form): void;
}
