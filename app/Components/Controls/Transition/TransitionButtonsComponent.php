<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Transition;

use FKSDB\Models\Events\Model\ApplicationHandlerException;
use FKSDB\Models\ORM\Columns\Types\EnumColumn;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionException;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Tracy\Debugger;

/**
 * @phpstan-template TModel of Model
 */
class TransitionButtonsComponent extends BaseComponent
{
    /** @phpstan-var Machine<ModelHolder<TModel,(FakeStringEnum&EnumColumn)>> */
    protected Machine $machine;
    /** @phpstan-var TModel Model */
    private Model $model;

    /**
     * @phpstan-param Machine<ModelHolder<TModel,(FakeStringEnum&EnumColumn)>> $machine
     * @phpstan-param TModel $model
     */
    public function __construct(Container $container, Machine $machine, Model $model)
    {
        parent::__construct($container);
        $this->model = $model;
        $this->machine = $machine;
    }

    final public function render(bool $showInfo = true): void
    {
        $holder = $this->machine->createHolder($this->model);
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'buttons.latte', [
            'showInfo' => $showInfo,
            'transitions' => $this->machine->getTransitions()->filterAvailable($holder)->toArray(),
            'holder' => $holder,
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function handleTransition(string $transitionName): void
    {
        $holder = $this->machine->createHolder($this->model);
        try {
            $transition = $this->machine->getTransitions()->filterById($transitionName)->select();
            $transition->execute($holder);
            $this->getPresenter()->flashMessage(
                $transition->getSuccessLabel(),
                Message::LVL_SUCCESS
            );
        } catch (
            ApplicationHandlerException
            | ForbiddenRequestException
            | UnavailableTransitionsException
            | UnavailableTransitionException $exception
        ) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        } catch (\Throwable$exception) {
            Debugger::log($exception, 'transitions');
            $this->getPresenter()->flashMessage(_('Some error emerged'), Message::LVL_ERROR);
        }
        $this->getPresenter()->redirect('this');
    }
}
