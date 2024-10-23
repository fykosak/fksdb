<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\FormComponent;

use FKSDB\Models\MachineCode\MachineCode;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
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
            $model = MachineCode::parseModelHash(
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
        $form->addText('code', _('Code'))->setRequired();
    }

    abstract protected function innerHandleSuccess(TeamModel2|PersonModel $model, Form $form): void;

    abstract protected function getSalt(): string;
}
