<?php

declare(strict_types=1);

namespace FKSDB\Components\MachineCode;

use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\Forms\Form;

abstract class FormComponent extends \FKSDB\Components\Controls\FormComponent\FormComponent
{
    final protected function handleSuccess(Form $form): void
    {
        try {
            /** @phpstan-var array{bypass:bool,code:string,} $values */
            $values = $form->getValues('array');
            $code = MachineCode::createFromCode($this->container, $values['code']);
            if (!$values['bypass']) {
                $code->check();
            }
            $this->innerHandleSuccess($code, $form);
        } catch (ForbiddenRequestException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        }
    }

    abstract protected function innerHandleSuccess(MachineCode $code, Form $form): void;

    abstract protected function innerConfigureForm(Form $form): void;

    final protected function configureForm(Form $form): void
    {
        $form->addText('code', _('Code'));
        $form->addCheckbox('bypass', _('Bypass checksum'));
        $this->innerConfigureForm($form);
    }
}
