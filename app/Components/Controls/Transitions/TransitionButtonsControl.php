<?php

namespace FKSDB\Components\Controls\Transitions;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Transitions\IStateModel;
use FKSDB\Transitions\Machine;
use FKSDB\Transitions\UnavailableTransitionsException;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nette\DI\Container;
use Tracy\Debugger;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;

/**
 * Class TransitionButtonsControl
 * @package FKSDB\Components\Controls\Transitions
 * @property FileTemplate $template
 */
class TransitionButtonsControl extends BaseComponent {
    /**
     * @var Machine
     */
    private $machine;
    /**
     * @var IStateModel
     */
    private $model;

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

    public function render() {
        $this->template->buttons = $this->machine->getAvailableTransitions($this->model);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'TransitionButtonsControl.latte');
        $this->template->render();
    }

    /**
     * @param $name
     * @throws AbortException
     */
    public function handleTransition($name) {
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
