<?php

namespace FKSDB\Components\Controls\Events;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Model\ApplicationHandlerFactory;
use FKSDB\Models\Events\Model\Grid\SingleEventSource;
use FKSDB\Models\Logging\FlashMessageDump;
use FKSDB\Models\Logging\MemoryLogger;
use FKSDB\Models\ORM\Models\ModelEvent;
use Nette\Application\AbortException;
use Nette\DI\Container;

/**
 * Class MassTransitionsControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class MassTransitionsComponent extends BaseComponent {

    private ModelEvent $event;

    private EventDispatchFactory $eventDispatchFactory;

    private ApplicationHandlerFactory $applicationHandlerFactory;

    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container);
        $this->event = $event;
    }

    final public function injectPrimary(EventDispatchFactory $eventDispatchFactory, ApplicationHandlerFactory $applicationHandlerFactory): void {
        $this->eventDispatchFactory = $eventDispatchFactory;
        $this->applicationHandlerFactory = $applicationHandlerFactory;
    }

    final public function render(): void {
        /** @var  $machine */
        $machine = $this->eventDispatchFactory->getEventMachine($this->event);
        $this->template->transitions = $machine->getPrimaryMachine()->getTransitions();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'layout.massTransitions.latte');
    }

    /**
     * @param string $name
     * @return void
     * @throws AbortException
     *
     * @throws NeonSchemaException
     */
    public function handleTransition(string $name): void {
        $source = new SingleEventSource($this->event, $this->getContext(), $this->eventDispatchFactory);
        $logger = new MemoryLogger();
        $total = 0;
        $errored = 0;
        foreach ($source->getHolders() as $key => $holder) {
            $handler = $this->applicationHandlerFactory->create($this->event, $logger);
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
