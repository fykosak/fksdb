<?php

namespace FKSDB\Events\Model;

use DuplicateApplicationException;
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
use FKSDB\Logging\ILogger;
use FKSDB\Messages\Message;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\Transitions\UnavailableTransitionException;
use FormUtils;
use Nette\Database\Connection;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Tracy\Debugger;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ApplicationHandler {

    const ERROR_ROLLBACK = 'rollback';
    const ERROR_SKIP = 'skip';
    const STATE_TRANSITION = 'transition';
    const STATE_OVERWRITE = 'overwrite';

    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * @var ILogger
     */
    private $logger;

    /**
     * @var int
     */
    private $errorMode = self::ERROR_ROLLBACK;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var Machine
     */
    private $machine;

    /**
     * ApplicationHandler constructor.
     * @param ModelEvent $event
     * @param ILogger $logger
     * @param Connection $connection
     * @param Container $container
     */
    function __construct(ModelEvent $event, ILogger $logger, Connection $connection, Container $container) {
        $this->event = $event;
        $this->logger = $logger;
        $this->connection = $connection;
        $this->container = $container;
    }

    /**
     * @return int
     */
    public function getErrorMode() {
        return $this->errorMode;
    }

    /**
     * @param $errorMode
     */
    public function setErrorMode($errorMode) {
        $this->errorMode = $errorMode;
    }

    /**
     * @param Holder $holder
     * @return Machine
     */
    public function getMachine(Holder $holder) {
        $this->initializeMachine($holder);
        return $this->machine;
    }

    /**
     * @return ILogger
     */
    public function getLogger() {
        return $this->logger;
    }

    /**
     * @param Holder $holder
     * @param $data
     * @throws JsonException
     */
    public final function store(Holder $holder, $data) {
        $this->_storeAndExecute($holder, $data, null, self::STATE_OVERWRITE);
    }

    /**
     * @param Holder $holder
     * @param Form|ArrayHash|null $data
     * @param mixed $explicitTransitionName
     * @throws JsonException
     */
    public function storeAndExecute(Holder $holder, $data = null, $explicitTransitionName = null) {
        $this->_storeAndExecute($holder, $data, $explicitTransitionName, self::STATE_TRANSITION);
    }

    /**
     * @param Holder $holder
     * @param string $explicitTransitionName
     */
    public function onlyExecute(Holder $holder, string $explicitTransitionName) {
        $this->initializeMachine($holder);

        try {
            $explicitMachineName = $this->machine->getPrimaryMachine()->getName();
            $this->beginTransaction();
            $transition = $this->machine->getBaseMachine($explicitMachineName)->getTransition($explicitTransitionName);
            if ($holder->getPrimaryHolder()->getModelState() !== $transition->getSource()) {
                throw new UnavailableTransitionException($transition, $holder->getPrimaryHolder()->getModel());
            }

            $transition->execute($holder);
            $holder->saveModels();
            $transition->executed($holder, []);

            $this->commit();

            if ($transition->isCreating()) {
                $this->logger->log(new Message(sprintf(_('Přihláška "%s" vytvořena.'), (string)$holder->getPrimaryHolder()->getModel()), ILogger::SUCCESS));
            } elseif ($transition->isTerminating()) {
                $this->logger->log(new Message(_('Přihláška smazána.'), ILogger::SUCCESS));
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
        } catch (DuplicateApplicationException $exception) {
            $message = $exception->getMessage();
            $this->logger->log(new Message($message, ILogger::ERROR));
            $this->reRaise($exception);
        } catch (MachineExecutionException $exception) {
            $this->logger->log(new Message($exception->getMessage(), ILogger::ERROR));
            $this->reRaise($exception);
        } catch (SubmitProcessingException $exception) {
            $this->logger->log(new Message($exception->getMessage(), ILogger::ERROR));
            $this->reRaise($exception);
        } catch (FullCapacityException $exception) {
            $this->logger->log(new Message($exception->getMessage(), ILogger::ERROR));
            $this->reRaise($exception);
        } catch (ExistingPaymentException $exception) {
            $this->logger->log(new Message($exception->getMessage(), ILogger::ERROR));
            $this->reRaise($exception);
        } catch (UnavailableTransitionException $exception) {
            $this->logger->log(new Message($exception->getMessage(), ILogger::ERROR));
            $this->reRaise($exception);
        }
    }

    /**
     * @param Holder $holder
     * @param $data
     * @param $explicitTransitionName
     * @param $execute
     * @throws JsonException
     */
    private function _storeAndExecute(Holder $holder, $data, $explicitTransitionName, $execute) {
        $this->initializeMachine($holder);

        try {
            $explicitMachineName = $this->machine->getPrimaryMachine()->getName();

            $this->beginTransaction();
            /** @var Transition[] $transitions */
            $transitions = [];
            // saved transition of baseModel/baseMachine/baseHolder/baseShit/base*
            if ($explicitTransitionName) {
                $transitions[$explicitMachineName] = $this->machine->getBaseMachine($explicitMachineName)->getTransition($explicitTransitionName);
            }

            if ($data) {
                $transitions = $this->processData($data, $transitions, $holder, $execute);
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
                $this->logger->log(new Message(_('Přihláška smazána.'), ILogger::SUCCESS));
            } elseif (isset($transitions[$explicitMachineName])) {
                $this->logger->log(new Message(sprintf(_('Stav přihlášky "%s" změněn.'), (string)$holder->getPrimaryHolder()->getModel()), ILogger::INFO));
            }
            if ($data && (!isset($transitions[$explicitMachineName]) || !$transitions[$explicitMachineName]->isTerminating())) {
                $this->logger->log(new Message(sprintf(_('Přihláška "%s" uložena.'), (string)$holder->getPrimaryHolder()->getModel()), ILogger::SUCCESS));
            }
        } catch (ModelDataConflictException $exception) {
            $container = $exception->getReferencedId()->getReferencedContainer();
            $container->setConflicts($exception->getConflicts());

            $message = sprintf(_('Některá pole skupiny "%s" neodpovídají existujícímu záznamu.'), $container->getOption('label'));
            $this->logger->log(new Message($message, ILogger::ERROR));
            $this->formRollback($data);
            $this->reRaise($exception);
        } catch (SecondaryModelDataConflictException $exception) {
            $message = sprintf(_('Data ve skupině "%s" kolidují s již existující přihláškou.'), $exception->getBaseHolder()->getLabel());
            Debugger::log($exception, 'app-conflict');
            $this->logger->log(new Message($message, ILogger::ERROR));
            $this->formRollback($data);
            $this->reRaise($exception);
        } catch (DuplicateApplicationException $exception) {
            $message = $exception->getMessage();
            $this->logger->log(new Message($message, ILogger::ERROR));
            $this->formRollback($data);
            $this->reRaise($exception);
        } catch (MachineExecutionException $exception) {
            $this->logger->log(new Message($exception->getMessage(), ILogger::ERROR));
            $this->formRollback($data);
            $this->reRaise($exception);
        } catch (SubmitProcessingException $exception) {
            $this->logger->log(new Message($exception->getMessage(), ILogger::ERROR));
            $this->formRollback($data);
            $this->reRaise($exception);
        } catch (FullCapacityException $exception) {
            $this->logger->log(new Message($exception->getMessage(), ILogger::ERROR));
            $this->formRollback($data);
            $this->reRaise($exception);
        } catch (ExistingPaymentException $exception) {
            $this->logger->log(new Message($exception->getMessage(), ILogger::ERROR));
            $this->formRollback($data);
            $this->reRaise($exception);
        }
    }

    /**
     * @param $data
     * @param $transitions
     * @param Holder $holder
     * @param $execute
     * @return mixed
     * @throws MachineExecutionException
     * @throws JsonException
     */
    private function processData($data, $transitions, Holder $holder, $execute) {
        if ($data instanceof Form) {
            $values = FormUtils::emptyStrToNull($data->getValues());
            $form = $data;
        } else {
            $values = $data;
            $form = null;
        }
        Debugger::log(Json::encode((array)$values), 'app-form');
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
                Debugger::barDump($state);
                Debugger::barDump($this->machine->getBaseMachine($name)->getTransitionByTarget($state, $newState), $name);
                $transition = $this->machine->getBaseMachine($name)->getTransitionByTarget($state, $newState);
                if ($transition) {
                    $transitions[$name] = $transition;
                } elseif (!($state == BaseMachine::STATE_INIT && $newState == BaseMachine::STATE_TERMINATED)) {
                    $msg = _('Ze stavu "%s" automatu "%s" neexistuje přechod do stavu "%s".');
                    throw new MachineExecutionException(sprintf($msg, $this->machine->getBaseMachine($name)->getStateName($state), $holder->getBaseHolder($name)->getLabel(), $this->machine->getBaseMachine($name)->getStateName($newState)));
                }
            }
        }
        return $transitions;
    }

    /**
     * @param Holder $holder
     */
    private function initializeMachine(Holder $holder) {
        if (!$this->machine) {
            $this->machine = $this->container->createEventMachine($this->event);
        }
    }

    /**
     * @param $data
     */
    private function formRollback($data) {
        if ($data instanceof Form) {
            foreach ($data->getComponents(true, ReferencedId::class) as $referencedId) {
                $referencedId->rollback();
            }
        }
        $this->rollback();
    }

    public function beginTransaction() {
        if (!$this->connection->getPdo()->inTransaction()) {
            $this->connection->beginTransaction();
        }
    }

    private function rollback() {
        if ($this->errorMode == self::ERROR_ROLLBACK) {
            $this->connection->rollBack();
        }
    }

    /**
     * @param bool $final
     */
    public function commit($final = false) {
        if ($this->connection->getPdo()->inTransaction() && ($this->errorMode == self::ERROR_ROLLBACK || $final)) {
            $this->connection->commit();
        }
    }

    /**
     * @param Exception $e
     */
    private function reRaise(Exception $e) {
        throw new ApplicationHandlerException(_('Chyba při ukládání přihlášky.'), null, $e);
    }
}
