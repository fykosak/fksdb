<?php

namespace FKSDB\Components\Controls\Transitions;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Models\Transitions\StateModel;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Transition\UnavailableTransitionsException;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Tracy\Debugger;

/**
 * Class TransitionButtonsControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TransitionButtonsComponent extends BaseComponent {

    private Machine $machine;

    private StateModel $model;

    public function __construct(Machine $machine, Container $container, StateModel $model) {
        parent::__construct($container);
        $this->machine = $machine;
        $this->model = $model;
    }

    public function render(): void {
        $this->template->buttons = $this->machine->getAvailableTransitions($this->model);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.transition.latte');
        $this->template->render();
    }

    /**
     * @param string $name
     * @throws AbortException
     */
    public function handleTransition(string $name): void {
        try {
            $this->machine->executeTransition($name, $this->model);
        } catch (ForbiddenRequestException | UnavailableTransitionsException$exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), BasePresenter::FLASH_ERROR);
            return;
        } catch (\Exception $exception) {
            Debugger::log($exception);
            $this->getPresenter()->flashMessage(_('Some error emerged'), BasePresenter::FLASH_ERROR);
        }
        $this->redirect('this');
    }
}
