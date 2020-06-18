<?php

namespace FKSDB\Components\Events;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Config\NeonSchemaException;
use FKSDB\Events\EventDispatchFactory;
use FKSDB\Events\Model\ApplicationHandlerFactory;
use FKSDB\Events\Model\Grid\SingleEventSource;
use FKSDB\Logging\FlashMessageDump;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

/**
 * Class MassTransitionsControl
 * @author Michal Červeňák <miso@fykos.cz>
 */
class MassTransitionsControl extends BaseComponent {
    /**
     * @var ModelEvent
     */
    private $event;
    /** @var EventDispatchFactory */
    private $eventDispatchFactory;
    /** @var ApplicationHandlerFactory */
    private $applicationHandlerFactory;

    /**
     * MassTransitionsControl constructor.
     * @param Container $container
     * @param ModelEvent $event
     */
    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container);
        $this->event = $event;
    }

    /**
     * @param EventDispatchFactory $eventDispatchFactory
     * @param ApplicationHandlerFactory $applicationHandlerFactory
     * @return void
     */
    public function injectPrimary(EventDispatchFactory $eventDispatchFactory, ApplicationHandlerFactory $applicationHandlerFactory) {
        $this->eventDispatchFactory = $eventDispatchFactory;
        $this->applicationHandlerFactory = $applicationHandlerFactory;
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function render() {
        /** @var  $machine */
        $machine = $this->eventDispatchFactory->getEventMachine($this->event);
        $this->template->transitions = $machine->getPrimaryMachine()->getTransitions();
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'MassTransitions.latte');
        $this->template->render();
    }

    /**
     * @param string $name
     * @return void
     * @throws NeonSchemaException
     * @throws BadRequestException
     * @throws AbortException
     */
    public function handleTransition(string $name) {
        $source = new SingleEventSource($this->event, $this->getContext());
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
