<?php

namespace FKSDB\Components\Controls\Events;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\Events\Machine\BaseMachine;
use FKSDB\Models\Events\Machine\Machine;
use FKSDB\Models\Events\Model\ApplicationHandler;
use FKSDB\Models\Events\Model\ApplicationHandlerException;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Models\Logging\FlashMessageDump;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Modules\Core\BasePresenter;
use Nette\Application\AbortException;
use Nette\DI\Container;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 * @method AuthenticatedPresenter|BasePresenter getPresenter($need = true)
 */
class TransitionButtonsComponent extends BaseComponent {

    private ApplicationHandler $handler;

    private Holder $holder;

    /**
     * ApplicationComponent constructor.
     * @param Container $container
     * @param ApplicationHandler $handler
     * @param Holder $holder
     */
    public function __construct(Container $container, ApplicationHandler $handler, Holder $holder) {
        parent::__construct($container);
        $this->handler = $handler;
        $this->holder = $holder;
    }

    final public function render(): void {
        $this->template->transitions = $this->getMachine()->getPrimaryMachine()->getAvailableTransitions($this->holder, $this->holder->getPrimaryHolder()->getModelState(), BaseMachine::EXECUTABLE or BaseMachine::VISIBLE);
        $this->template->holder = $this->holder;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.application.inline.latte');
    }

    /**
     * @param string $transitionName
     * @throws AbortException
     */
    public function handleTransition(string $transitionName): void {
        try {
            $this->handler->onlyExecute($this->holder, $transitionName);
            FlashMessageDump::dump($this->handler->getLogger(), $this->getPresenter());
            $this->redirect('this');
        } catch (ApplicationHandlerException $exception) {
            /* handled elsewhere, here it's to just prevent redirect */
            FlashMessageDump::dump($this->handler->getLogger(), $this->getPresenter());
        }
    }

    private function getMachine(): Machine {
        return $this->handler->getMachine();
    }
}
