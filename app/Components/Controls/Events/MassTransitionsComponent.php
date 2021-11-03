<?php

namespace FKSDB\Components\Controls\Events;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\Events\Model\ApplicationHandler;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Model\Grid\SingleEventSource;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\DI\Container;

class MassTransitionsComponent extends BaseComponent {

    private ModelEvent $event;

    private EventDispatchFactory $eventDispatchFactory;

    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container);
        $this->event = $event;
    }

    final public function injectPrimary(EventDispatchFactory $eventDispatchFactory): void {
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    final public function render(): void {
        /** @var  $machine */
        $machine = $this->eventDispatchFactory->getEventMachine($this->event);
        $this->template->transitions = $machine->getPrimaryMachine()->getTransitions();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.massTransitions.latte');
    }

    /**
     * @throws NeonSchemaException
     */
    public function handleTransition(string $name): void {
        $source = new SingleEventSource($this->event, $this->getContext(), $this->eventDispatchFactory);
        $logger = new MemoryLogger();
        $total = 0;
        $errored = 0;
        foreach ($source->getHolders() as $key => $holder) {
            $handler = new ApplicationHandler($this->event, $logger, $this->getContext());
            $total++;
            try {
                $handler->onlyExecute($holder, $name);
            } catch (\Exception $exception) {
                $errored++;
            }
        }
        FlashMessageDump::dump($logger, $this->getPresenter(), true);
        $this->getPresenter()->flashMessage(sprintf(
            _('Total %d applications, state changed %d, unavailable %d. '),
            $total,
            $total - $errored,
            $errored
        ));
        $this->getPresenter()->redirect('this');
    }
}
