<?php

namespace FKSDB\Components\Controls\Transitions;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Tracy\Debugger;

class TransitionButtonsComponent extends BaseComponent {

    private Machine $machine;
    private ModelHolder $model;

    public function __construct(Machine $machine, Container $container, ModelHolder $model) {
        parent::__construct($container);
        $this->machine = $machine;
        $this->model = $model;
    }

    final public function render(): void {
        $this->template->buttons = $this->machine->getAvailableTransitions($this->model);
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.transition.latte');
    }

    public function handleTransition(string $name): void {
        try {
            $this->machine->executeTransitionById($name, $this->model);
        } catch (ForbiddenRequestException | UnavailableTransitionsException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            return;
        } catch (\Exception $exception) {
            Debugger::log($exception);
            $this->getPresenter()->flashMessage(_('Some error emerged'), Message::LVL_ERROR);
        }
        $this->redirect('this');
    }
}
