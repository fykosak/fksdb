<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Events;

use FKSDB\Components\CodeProcessing\CodeFormComponent;
use Nette\Forms\Form;

class FastEditComponent extends CodeFormComponent
{
    final public function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'edit.latte';
    }

    protected function innerHandleSuccess(string $id, Form $form): void
    {
        $this->getPresenter()->redirect('edit', ['id' => $id]);
    }

    protected function innerConfigureForm(Form $form): void
    {
        $form->elementPrototype->target('_blank');
    }

    protected function appendSubmitButton(Form $form): void
    {
        $form->addSubmit('edit', _('Edit'));
    }
}
