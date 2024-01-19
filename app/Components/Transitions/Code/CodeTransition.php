<?php

declare(strict_types=1);

namespace FKSDB\Components\Transitions\Code;

use FKSDB\Components\Controls\FormComponent\FormComponent;
use FKSDB\Models\MachineCode\MachineCode;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\AbortException;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;

/**
 * @phpstan-import-type TSupportedModel from MachineCode
 * @phpstan-template TModel of Model
 * @phpstan-type TState (\FKSDB\Models\Utils\FakeStringEnum&\FKSDB\Models\ORM\Columns\Types\EnumColumn)
 * @phpstan-type TMachine Machine<ModelHolder<TState,TModel>>
 */
abstract class CodeTransition extends FormComponent
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
     * @phpstan-return Transition<ModelHolder<TState,TModel>>
     */
    protected function getTransitions(): array
    {
        return Machine::filterByTarget(
            $this->machine->transitions,
            $this->targetState
        );
    }

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
            $newModel = $this->resolveModel($model);
            $holder = $this->machine->createHolder($newModel);

            $transition = Machine::selectTransition(
                Machine::filterAvailable($this->getTransitions(), $holder)
            );
            $this->machine->execute($transition, $holder);

            $this->flashMessage(_('Transition successful'), Message::LVL_SUCCESS);
            $this->getPresenter()->redirect('this');
        } catch (AbortException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            $this->getPresenter()->redirect('this');
        }
    }

    protected function appendSubmitButton(Form $form): SubmitButton
    {
        $transitions = $this->getTransitions();
        return $form->addSubmit('submit', reset($transitions)->label());
    }

    protected function configureForm(Form $form): void
    {
        $form->addText('code', _('Code'))->setRequired();
    }

    /**
     * @phpstan-param TSupportedModel $model
     * @phpstan-return TModel
     */
    abstract protected function resolveModel(Model $model): Model;

    abstract protected function getSalt(): string;
}
