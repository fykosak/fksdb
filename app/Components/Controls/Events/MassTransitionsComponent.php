<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Events;

use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Model\ApplicationHandler;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use Nette\DI\Container;

class MassTransitionsComponent extends BaseComponent
{
    private EventModel $event;
    private EventDispatchFactory $eventDispatchFactory;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
    }

    final public function injectPrimary(EventDispatchFactory $eventDispatchFactory): void
    {
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    final public function render(): void
    {
        $machine = $this->eventDispatchFactory->getEventMachine($this->event);
        $this->template->transitions = $machine->getTransitions();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.massTransitions.latte');
    }

    public function handleTransition(string $name): void
    {
        $logger = new MemoryLogger();
        $total = 0;
        $errored = 0;
        $machine = $this->eventDispatchFactory->getEventMachine($this->event);
        /** @var EventParticipantModel $model */
        foreach ($this->event->getParticipants() as $model) {
            $holder = $machine->createHolder($model);
            $handler = new ApplicationHandler($this->event, $logger, $this->getContext());
            $total++;
            try {
                $handler->onlyExecute($holder, $name);
            } catch (\Throwable $exception) {
                $errored++;
            }
        }
        FlashMessageDump::dump($logger, $this->getPresenter());
        $this->getPresenter()->flashMessage(
            sprintf(
                _('Total %d applications, state changed %d, unavailable %d. '),
                $total,
                $total - $errored,
                $errored
            )
        );
        $this->getPresenter()->redirect('this');
    }
}
