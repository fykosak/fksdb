<?php

declare(strict_types=1);

namespace FKSDB\Components\CodeProcessing;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\Forms\Form;

abstract class CodeFormComponent extends FormComponent
{

    final protected function handleSuccess(Form $form): void
    {
        try {
            $values = $form->getValues('array');
            if ($values['bypass']) {
                $id = CodeValidator::bypassCode($values['code']);
            } else {
                $id = CodeValidator::checkCode($this->container, $values['code']);
            }
            $this->innerHandleSuccess($id, $form);
        } catch (ForbiddenRequestException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        }
    }

    abstract protected function innerHandleSuccess(string $id, Form $form): void;

    abstract protected function innerConfigureForm(Form $form): void;

    final protected function configureForm(Form $form): void
    {
        $form->addText('code', _('Code'));
        $form->addCheckbox('bypass', _('Bypass checksum'));
        $this->innerConfigureForm($form);
    }
}
