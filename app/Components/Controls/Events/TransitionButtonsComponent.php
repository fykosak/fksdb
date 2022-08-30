<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Events;

use FKSDB\Models\Events\Model\Holder\BaseHolder;
use Fykosak\Utils\BaseComponent\BaseComponent;
use FKSDB\Models\Events\Model\ApplicationHandler;
use FKSDB\Models\Events\Model\ApplicationHandlerException;
use Fykosak\Utils\Logging\FlashMessageDump;
use Nette\DI\Container;

class TransitionButtonsComponent extends BaseComponent
{

    private ApplicationHandler $handler;
    private BaseHolder $holder;

    public function __construct(Container $container, ApplicationHandler $handler, BaseHolder $holder)
    {
        parent::__construct($container);
        $this->handler = $handler;
        $this->holder = $holder;
    }

    final public function render(): void
    {
        $this->template->transitions = $this->handler->getMachine()->getAvailableTransitions(
            $this->holder,
            $this->holder->getModelState(),
            true,
            true
        );
        $this->template->holder = $this->holder;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.application.inline.latte');
    }

    public function handleTransition(string $transitionName): void
    {
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
