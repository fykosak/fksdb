<?php

namespace FKSDB\Events\Model;

use FKSDB\ORM\Services\Exception\DuplicateApplicationException;
use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Machine\Transition;
use FKSDB\Events\MachineExecutionException;
use FKSDB\Events\Model\Holder\BaseHolder;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Events\Model\Holder\SecondaryModelStrategies\SecondaryModelDataConflictException;
use FKSDB\Events\SubmitProcessingException;
use Exception;
use FKSDB\Components\Forms\Controls\ModelDataConflictException;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Controls\Schedule\ExistingPaymentException;
use FKSDB\Components\Forms\Controls\Schedule\FullCapacityException;
use FKSDB\Events\EventDispatchFactory;
use FKSDB\Logging\ILogger;
use FKSDB\Messages\Message;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\Transitions\UnavailableTransitionException;
use FKSDB\Utils\FormUtils;
use Nette\Database\Connection;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ApplicationHandler {

    public const ERROR_ROLLBACK = 'rollback';
    public const ERROR_SKIP = 'skip';
    public const STATE_TRANSITION = 'transition';
    public const STATE_OVERWRITE = 'overwrite';

    private ModelEvent $event;

    private ILogger $logger;

    private string $errorMode = self::ERROR_ROLLBACK;

    private Connection $connection;

    private Container $container;

    private Machine $machine;

    private EventDispatchFactory $eventDispatchFactory;

    public function __construct(ModelEvent $event, ILogger $logger, Connection $connection, Container $container, EventDispatchFactory $eventDispatchFactory) {
        $this->event = $event;
        $this->logger = $logger;
        $this->connection = $connection;
        $this->container = $container;
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    public function getErrorMode(): string {
        return $this->errorMode;
    }

    public function setErrorMode(string $errorMode): void {
        $this->errorMode = $errorMode;
    }

    public function getMachine(): Machine {
        $this->initializeMachine();
        return $this->machine;
    }

    public function getLogger(): ILogger {
        return $this->logger;
    }

    final public function store(Holder $holder, ArrayHash $data): void {
        $this->innerStoreAndExecute($holder, $data, null, null, self::STATE_OVERWRITE);
    }

    final public function storeAndExecuteValues(Holder $holder, ArrayHash $data): void {
        $this->innerStoreAndExecute($holder, $data, null, null, self::STATE_TRANSITION);
    }

    final public function storeAndExecuteForm(Holder $holder, Form $form, ?string $explicitTransitionName = null): void {
        $this->innerStoreAndExecute($holder, null, $form, $explicitTransitionName, self::STATE_TRANSITION);
    }

    final public function onlyExecute(Holder $holder, string $explicitTransitionName): void {
        $this->initializeMachine();

        try {
            $this->beginTransaction();
            $transition = $this->machine->getPrimaryMachine()->getTransition($explicitTransitionName);
            if (!$transition->matches($holder->getPrimaryHolder()->getModelState())) {
                throw new UnavailableTransitionException($transition, $holder->getPrimaryHolder()->getModel());
            }

            $transition->execute($holder);
            $holder->saveModels();
            $transition->executed($holder, []);

            $this->commit();

            if ($transition->isCreating()) {
                $this->logger->log(new Message(sprintf(_('Přihláška "%s" vytvořena.'), (string)$holder->getPrimaryHolder()->getModel()), ILogger::SUCCESS));
            } elseif ($transition->isTerminating()) {
                $this->logger->log(new Message(_('Application deleted.'), ILogger::SUCCESS));
            } elseif (isset($transition)) {
                $this->logger->log(new Message(sprintf(_('Stav přihlášky "%s" změněn.'), (string)$holder->getPrimaryHolder()->getModel()), ILogger::INFO));
            }
        } catch (ModelDataConflictException $exception) {
            $container = $exception->getReferencedId()->getReferencedContainer();
            $container->setConflicts($exception->getConflicts());

            $message = sprintf(_('Některá pole skupiny "%s" neodpovídají existujícímu záznamu.'), $container->getOption('label'));
            $this->logger->log(new Message($message, ILogger::ERROR));
            $this->reRaise($exception);
        } catch (SecondaryModelDataConflictException $exception) {
            $message = sprintf(_('Data ve skupině "%s" kolidují s již existující přihláškou.'), $exception->getBaseHolder()->getLabel());
            Debugger::log($exception, 'app-conflict');
            $this->logger->log(new Message($message, ILogger::ERROR));
            $this->reRaise($exception);
        } catch (DuplicateApplicationException|MachineExecutionException|SubmitProcessingException|FullCapacityException|ExistingPaymentException|UnavailableTransitionException $exception) {
            $this->logger->log(new Message($exception->getMessage(), ILogger::ERROR));
            $this->reRaise($exception);
        }
    }

    private function innerStoreAndExecute(Holder $holder, ?ArrayHash $data, ?Form $form, ?string $explicitTransitionName, ?string $execute): void {
        $this->initializeMachine();

        try {
            $explicitMachineName = $this->machine->getPrimaryMachine()->getName();

            $this->beginTransaction();
            /** @var Transition[] $transitions */
            $transitions = [];
            // saved transition of baseModel/baseMachine/baseHolder/baseShit/base*
            if ($explicitTransitionName) {
                $transitions[$explicitMachineName] = $this->machine->getBaseMachine($explicitMachineName)->getTransition($explicitTransitionName);
            }

            if ($data || $form) {
                $transitions = $this->processData($data, $form, $transitions, $holder, $execute);
            }

            if ($execute == self::STATE_OVERWRITE) {
                foreach ($holder->getBaseHolders() as $name => $baseHolder) {
                    if (isset($data[$name][BaseHolder::STATE_COLUMN])) {
                        $baseHolder->setModelState($data[$name][BaseHolder::STATE_COLUMN]);
                    }
                }
            }

            $induced = []; // cache induced transition as they won't match after execution
            foreach ($transitions as $key => $transition) {
                $induced[$key] = $transition->execute($holder);
            }

            $holder->saveModels();

            foreach ($transitions as $key => $transition) {
                $transition->executed($holder, $induced[$key]); //note the 'd', it only triggers onExecuted event
            }

            $this->commit();

            if (isset($transitions[$explicitMachineName]) && $transitions[$explicitMachineName]->isCreating()) {
                $this->logger->log(new Message(sprintf(_('Přihláška "%s" vytvořena.'), (string)$holder->getPrimaryHolder()->getModel()), ILogger::SUCCESS));
            } elseif (isset($transitions[$explicitMachineName]) && $transitions[$explicitMachineName]->isTerminating()) {
                //$this->logger->log(sprintf(_("Přihláška '%s' smazána."), (string) $holder->getPrimaryHolder()->getModel()), ILogger::SUCCESS);
                $this->logger->log(new Message(_('Application deleted.'), ILogger::SUCCESS));
            } elseif (isset($transitions[$explicitMachineName])) {
                $this->logger->log(new Message(sprintf(_('Stav přihlášky "%s" změněn.'), (string)$holder->getPrimaryHolder()->getModel()), ILogger::INFO));
            }
            if ($data && (!isset($transitions[$explicitMachineName]) || !$transitions[$explicitMachineName]->isTerminating())) {
                $this->logger->log(new Message(sprintf(_('Application "%s" saved.'), (string)$holder->getPrimaryHolder()->getModel()), ILogger::SUCCESS));
            }
        } catch (ModelDataConflictException $exception) {
            $container = $exception->getReferencedId()->getReferencedContainer();
            $container->setConflicts($exception->getConflicts());
            $message = sprintf(_('Některá pole skupiny "%s" neodpovídají existujícímu záznamu.'), $container->getOption('label'));
            $this->logger->log(new Message($message, ILogger::ERROR));
            $this->formRollback($form);
            $this->reRaise($exception);
        } catch (SecondaryModelDataConflictException $exception) {
            $message = sprintf(_('Data ve skupině "%s" kolidují s již existující přihláškou.'), $exception->getBaseHolder()->getLabel());
            Debugger::log($exception, 'app-conflict');
            $this->logger->log(new Message($message, ILogger::ERROR));
            $this->formRollback($form);
            $this->reRaise($exception);
        } catch (DuplicateApplicationException|MachineExecutionException|SubmitProcessingException|FullCapacityException|ExistingPaymentException $exception) {
            $this->logger->log(new Message($exception->getMessage(), ILogger::ERROR));
            $this->formRollback($form);
            $this->reRaise($exception);
        }
    }

    private function processData(?ArrayHash $data, ?Form $form, array $transitions, Holder $holder, ?string $execute): array {
        if ($form) {
            $values = FormUtils::emptyStrToNull($form->getValues());
        } else {
            $values = $data;
        }
        Debugger::log(json_encode((array)$values), 'app-form');
        $primaryName = $holder->getPrimaryHolder()->getName();
        $newStates = [];
        if (isset($values[$primaryName][BaseHolder::STATE_COLUMN])) {
            $newStates[$primaryName] = $values[$primaryName][BaseHolder::STATE_COLUMN];
        }
        // Find out transitions
        $newStates = array_merge($newStates, $holder->processFormValues($values, $this->machine, $transitions, $this->logger, $form));
        if ($execute == self::STATE_TRANSITION) {
            foreach ($newStates as $name => $newState) {
                $state = $holder->getBaseHolder($name)->getModelState();
                $transition = $this->machine->getBaseMachine($name)->getTransitionByTarget($state, $newState);
                if ($transition) {
                    $transitions[$name] = $transition;
                } elseif (!($state == BaseMachine::STATE_INIT && $newState == BaseMachine::STATE_TERMINATED)) {
                    $msg = _('There is not a transition from state "%s" of machine "%s" to state "%s".');
                    throw new MachineExecutionException(sprintf($msg, $this->machine->getBaseMachine($name)->getStateName($state), $holder->getBaseHolder($name)->getLabel(), $this->machine->getBaseMachine($name)->getStateName($newState)));
                }
            }
        }
        return $transitions;
    }

    private function initializeMachine(): void {
        if (!isset($this->machine)) {
            $this->machine = $this->eventDispatchFactory->getEventMachine($this->event);
        }
    }

    private function formRollback(?Form $form): void {
        if ($form) {
            /** @var ReferencedId $referencedId */
            foreach ($form->getComponents(true, ReferencedId::class) as $referencedId) {
                $referencedId->rollback();
            }
        }
        $this->rollback();
    }

    public function beginTransaction(): void {
        if (!$this->connection->getPdo()->inTransaction()) {
            $this->connection->beginTransaction();
        }
    }

    private function rollback(): void {
        if ($this->errorMode == self::ERROR_ROLLBACK) {
            $this->connection->rollBack();
        }
    }

    public function commit(bool $final = false): void {
        if ($this->connection->getPdo()->inTransaction() && ($this->errorMode == self::ERROR_ROLLBACK || $final)) {
            $this->connection->commit();
        }
    }

    /**
     * @param Exception $e
     * @return void
     * @throws ApplicationHandlerException
     */
    private function reRaise(Exception $e): void {
        throw new ApplicationHandlerException(_('Error while saving the application.'), null, $e);
    }
}
