<?php

namespace FKSDB\Components\Controls\Events;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\Events\Model\ApplicationHandler;
use FKSDB\Models\Events\Model\ApplicationHandlerException;
use FKSDB\Models\Events\Model\Holder\Holder;
use Fykosak\Utils\Logging\FlashMessageDump;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Modules\Core\BasePresenter;
use Nette\DI\Container;

/**
 * @method AuthenticatedPresenter|BasePresenter getPresenter($need = true)
 */
class TransitionButtonsComponent extends BaseComponent {

    private ApplicationHandler $handler;

    private Holder $holder;

    public function __construct(Container $container, ApplicationHandler $handler, Holder $holder) {
        parent::__construct($container);
        $this->handler = $handler;
        $this->holder = $holder;
    }

    final public function render(): void {
        $this->template->transitions = $this->handler->getMachine()->getPrimaryMachine()->getAvailableTransitions($this->holder, $this->holder->getPrimaryHolder()->getModelState(), true, true);
        $this->template->holder = $this->holder;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.application.inline.latte');
    }

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
}
