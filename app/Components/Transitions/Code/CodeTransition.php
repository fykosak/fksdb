<?php

declare(strict_types=1);

namespace FKSDB\Components\Transitions\Code;

use FKSDB\Components\Controls\FormComponent\CodeForm;
use FKSDB\Models\MachineCode\MachineCode;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Machine\TransitionsSelection;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

/**
 * @phpstan-import-type TSupportedModel from MachineCode
 * @phpstan-template TModel of Model
 * @phpstan-type TState (\FKSDB\Models\Utils\FakeStringEnum&\FKSDB\Models\ORM\Columns\Types\EnumColumn)
 * @phpstan-type TMachine Machine<ModelHolder<TModel,TState>>
 */
abstract class CodeTransition extends CodeForm
{
    /** @phpstan-var TMachine */
    protected Machine $machine;

    /** @phpstan-var TState */
    protected FakeStringEnum $targetState;

    /**
     * @phpstan-param TState $targetState
     * @phpstan-param TMachine $machine
     */
    public function __construct(
        Container $container,
        FakeStringEnum $targetState,
        Machine $machine
    ) {
        parent::__construct($container);
        $this->targetState = $targetState;
        $this->machine = $machine;
    }

    /**
     * @phpstan-return TransitionsSelection<ModelHolder<TModel,TState>>
     */
    protected function getTransitions(): TransitionsSelection
    {
        return $this->machine->getTransitions()->filterByTarget($this->targetState);
    }

    /**
     * @throws \Throwable
     */
    final protected function innerHandleSuccess(Model $model, Form $form): void
    {
        $holder = $this->machine->createHolder($this->resolveModel($model));

        $transition = $this->getTransitions()->filterAvailable($holder)->select();
        $transition->execute($holder);

        $this->getPresenter()->flashMessage(_('Transition successful'), Message::LVL_SUCCESS);
        $this->finalRedirect();
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        return $form->addSubmit('submit', _('button.submit'));
    }

    /**
     * @phpstan-param TSupportedModel $model
     * @phpstan-return TModel
     */
    abstract protected function resolveModel(Model $model): Model;

    abstract protected function finalRedirect(): void;
}
