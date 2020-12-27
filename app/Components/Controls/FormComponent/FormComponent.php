<?php

namespace FKSDB\Components\Controls\FormComponent;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Exceptions\BadTypeException;
use Nette\Application\AbortException;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

/**
 * Class FormComponent
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class FormComponent extends BaseComponent {

    public function render(): void {
        $this->template->setFile($this->getTemplatePath());
        $this->template->render();
    }

    protected function getTemplatePath(): string {
        return __DIR__ . DIRECTORY_SEPARATOR . 'layout.latte';
    }

    protected function createFormControl(): FormControl {
        return new FormControl($this->getContext());
    }

    /**
     * @return Form
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
     * @return FormControl
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

    /**
     * @param SubmitButton $button
     * @return void
     * @throws AbortException
     */
    abstract protected function handleSuccess(SubmitButton $button): void;

    abstract protected function appendSubmitButton(Form $form): SubmitButton;

    abstract protected function configureForm(Form $form): void;
}
