<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\FormComponent;

use FKSDB\Components\MachineCode\MachineCode;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\AbortException;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

abstract class CodeForm extends FormComponent
{
    protected function handleSuccess(Form $form): void
    {
        /** @phpstan-var array{code:string} $values */
        $values = $form->getValues('array');
        try {
            $model = MachineCode::parseHash(
                $this->container,
                $values['code'],
                $this->getSalt()
            );
            $this->innerHandleSuccess($model, $form);
        } catch (AbortException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            $this->getPresenter()->redirect('this');
        }
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', _('button.submit'));
    }

    protected function configureForm(Form $form): void
    {
        $form->addText('code', _('Code'))->setRequired(true);
    }

    abstract protected function innerHandleSuccess(Model $model, Form $form): void;

    abstract protected function getSalt(): string;
}
