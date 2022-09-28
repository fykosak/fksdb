<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Transitions;

use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Tracy\Debugger;

class TransitionButtonsComponent extends BaseComponent
{

    private Machine $machine;
    private ModelHolder $holder;

    public function __construct(Machine $machine, Container $container, ModelHolder $model)
    {
        parent::__construct($container);
        $this->machine = $machine;
        $this->holder = $model;
    }

    final public function render(): void
    {
        $this->getTemplate()->transitions = $this->machine->getAvailableTransitions($this->holder);
        $this->getTemplate()->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.transition.latte');
    }

    /**
     * @throws AbortException
     */
    public function handleTransition(string $name): void
    {
        try {
            $this->machine->executeTransitionById($name, $this->holder);
        } catch (ForbiddenRequestException | UnavailableTransitionsException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            return;
        } catch (\Throwable $exception) {
            Debugger::log($exception);
            $this->getPresenter()->flashMessage(_('Some error emerged'), Message::LVL_ERROR);
        }
        $this->redirect('this');
    }
}
