<?php

declare(strict_types=1);

namespace FKSDB\Components\Transitions\Code;

use FKSDB\Components\Controls\FormComponent\CodeForm;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
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
 * @phpstan-template TModel of Model
 * @phpstan-type TState (FakeStringEnum&EnumColumn)
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
    final protected function innerHandleSuccess(TeamModel2|PersonModel $model, Form $form): void
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
     * @phpstan-return TModel
     */
    abstract protected function resolveModel(TeamModel2|PersonModel $model): Model;

    abstract protected function finalRedirect(): void;
}
