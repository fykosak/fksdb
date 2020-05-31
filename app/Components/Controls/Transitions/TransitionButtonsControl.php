<?php

namespace FKSDB\Components\Controls\Transitions;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Transitions\IStateModel;
use FKSDB\Transitions\Machine;
use FKSDB\Transitions\UnavailableTransitionsException;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Tracy\Debugger;

class TransitionButtonsControl extends BaseComponent {

    private Machine $machine;

    private IStateModel $model;

    /**
     * TransitionButtonsControl constructor.
     * @param Machine $machine
     * @param Container $container
     * @param IStateModel $model
     */
    public function __construct(Machine $machine, Container $container, IStateModel $model) {
        parent::__construct($container);
        $this->machine = $machine;
        $this->model = $model;
    }

    public function render(): void {
        $this->template->buttons = $this->machine->getAvailableTransitions($this->model);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'TransitionButtonsControl.latte');
        $this->template->render();
    }

    /**
     * @param string $name
     * @return void
     * @throws AbortException
     */
    public function handleTransition(string $name): void {
        try {
            $this->machine->executeTransition($name, $this->model);
        } catch (ForbiddenRequestException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), \BasePresenter::FLASH_ERROR);
            return;
        } catch (UnavailableTransitionsException $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), \BasePresenter::FLASH_ERROR);
            return;
        } catch (\Exception $exception) {
            Debugger::log($exception);
            $this->getPresenter()->flashMessage(_('Nastala chyba'), \BasePresenter::FLASH_ERROR);
        }
        $this->redirect('this');
    }
}
