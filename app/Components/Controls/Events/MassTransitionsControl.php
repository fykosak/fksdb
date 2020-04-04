<?php

namespace FKSDB\Components\Events;

use Events\Machine\Machine;
use Events\Model\ApplicationHandlerFactory;
use Events\Model\Grid\SingleEventSource;
use FKSDB\Logging\FlashMessageDump;
use FKSDB\Logging\MemoryLogger;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\AbortException;
use Nette\Application\UI\Control;
use Nette\DI\Container;
use Nette\Localization\ITranslator;
use Nette\Templating\FileTemplate;
use Tracy\Debugger;

/**
 * Class MassTransitionsControl
 * @package FKSDB\Components\Events
 * @property FileTemplate $template
 */
class MassTransitionsControl extends Control {
    /**
     * @var ModelEvent
     */
    private $event;
    /**
     * @var Container
     */
    private $container;

    /**
     * MassTransitionsControl constructor.
     * @param Container $container
     * @param ModelEvent $event
     */
    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct();
        $this->event = $event;
        $this->container = $container;
    }

    public function render() {
        /**
         * @var Machine $machine
         * @var ITranslator $translator
         */
        $machine = $this->container->createEventMachine($this->event);
        $this->template->transitions = $machine->getPrimaryMachine()->getTransitions();
        $translator = $this->container->getByType(ITranslator::class);
        $this->template->setTranslator($translator);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'MassTransitions.latte');
        $this->template->render();
    }

    /**
     * @param string $name
     * @throws AbortException
     */
    public function handleTransition(string $name) {
        $source = new SingleEventSource($this->event, $this->container);
        /** @var ApplicationHandlerFactory $applicationHandlerFactory */
        $applicationHandlerFactory = $this->container->getByType(ApplicationHandlerFactory::class);
        $logger = new MemoryLogger();
        foreach ($source as $key => $holder) {
            $handler = $applicationHandlerFactory->create($this->event, $logger);
            try {
                $handler->storeAndExecute($holder, null, $name);
            } catch (\Exception $exception) {
                Debugger::barDump($exception);
            }
        }
        FlashMessageDump::dump($logger, $this->getPresenter(), true);
        $this->getPresenter()->redirect('this');
    }
}
