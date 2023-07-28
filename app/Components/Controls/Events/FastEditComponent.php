<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Events;

use FKSDB\Components\MachineCode\MachineCode;
use FKSDB\Components\MachineCode\MachineCodeFormComponent;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

class FastEditComponent extends MachineCodeFormComponent
{
    final public function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'edit.latte';
    }

    protected function innerHandleSuccess(MachineCode $code, Form $form): void
    {
        $this->getPresenter()->redirect('edit', ['id' => $code->id]);
    }

    protected function innerConfigureForm(Form $form): void
    {
        $form->elementPrototype->target('_blank');
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('edit', _('Edit'));
    }
}
