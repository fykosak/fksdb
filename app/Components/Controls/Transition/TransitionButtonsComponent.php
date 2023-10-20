<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Transition;

use FKSDB\Models\Events\Model\ApplicationHandlerException;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;

/**
 * @phpstan-template THolder of ModelHolder
 */
class TransitionButtonsComponent extends BaseComponent
{
    /** @phpstan-var Machine<THolder> */
    protected Machine $machine;

    /** @phpstan-var THolder */
    private ModelHolder $holder;

    /**
     * @phpstan-param Machine<THolder> $machine
     * @phpstan-param THolder $holder
     */
    public function __construct(Container $container, Machine $machine, ModelHolder $holder)
    {
        parent::__construct($container);
        $this->holder = $holder;
        $this->machine = $machine;
    }

    final public function render(bool $showInfo = true): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'buttons.latte', [
            'showInfo' => $showInfo,
            'transitions' => Machine::filterAvailable($this->machine->transitions, $this->holder),
            'holder' => $this->holder,
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function handleTransition(string $transitionName): void
    {
        try {
            $transition = $this->machine->getTransitionById($transitionName);
            $this->machine->execute($transition, $this->holder);
            $this->getPresenter()->flashMessage(_('Transition successful'), Message::LVL_SUCCESS);
        } catch (ApplicationHandlerException | ForbiddenRequestException | UnavailableTransitionsException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        } catch (\Throwable$exception) {
            $this->getPresenter()->flashMessage(_('Some error emerged'), Message::LVL_ERROR);
        }
        $this->getPresenter()->redirect('this');
    }
}
