<?php

namespace Events\Model;

use DuplicateApplicationException;
use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\MachineExecutionException;
use Events\Model\Holder\BaseHolder;
use Events\Model\Holder\Holder;
use Events\Model\Holder\SecondaryModelStrategies\SecondaryModelDataConflictException;
use Events\SubmitProcessingException;
use Exception;
use FKSDB\Components\Forms\Controls\ModelDataConflictException;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\ModelEvent;
use FormUtils;
use Nette\Utils\ArrayHash;
use Nette\Database\Connection;
use Nette\DI\Container;
use Nette\Forms\Form;
use Submits\StorageException;

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

    function __construct(ModelEvent $event, ILogger $logger, Connection $connection, Container $container) {
        $this->event = $event;
        $this->logger = $logger;
        $this->connection = $connection;
        $this->container = $container;
    }

    public function getErrorMode() {
        return $this->errorMode;
    }

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

    public function getLogger() {
        return $this->logger;
    }

    public final function store(Holder $holder, $data) {
        $this->_storeAndExecute($holder, $data, null, null, self::STATE_OVERWRITE);
    }

    /**
     * @param Holder $holder
     * @param Form|ArrayHash|null $data
     * @param mixed $explicitTransitionName
     * @param mixed $explicitMachineName
     */
    public function storeAndExecute(Holder $holder, $data = null, $explicitTransitionName = null, $explicitMachineName = null) {
        $this->_storeAndExecute($holder, $data, $explicitTransitionName, $explicitMachineName, self::STATE_TRANSITION);
    }

    private function _storeAndExecute(Holder $holder, $data, $explicitTransitionName, $explicitMachineName, $execute) {
        $this->initializeMachine($holder);

        try {
            $explicitMachineName = $explicitMachineName ?: $this->machine->getPrimaryMachine()->getName();

            $this->beginTransaction();

            $transitions = [];
            if ($explicitTransitionName !== null) {
                $explicitMachine = $this->machine[$explicitMachineName];
                $explicitTransition = $explicitMachine->getTransition($explicitTransitionName);

                $transitions[$explicitMachineName] = $explicitTransition;
            }

            if ($data) {
                $transitions = $this->processData($data, $transitions, $holder, $execute);
            }

            if ($execute == self::STATE_OVERWRITE) {
                foreach ($holder as $name => $baseHolder) {
                    if (isset($data[$name][BaseHolder::STATE_COLUMN])) {
                        $baseHolder->setModelState($data[$name][BaseHolder::STATE_COLUMN]);
                    }
                }
            }

            $induced = []; // cache induced transition as they won't match after execution
            foreach ($transitions as $key => $transition) {
                $induced[$key] = $transition->execute();
            }

            $holder->saveModels();

            foreach ($transitions as $key => $transition) {
                $transition->executed($induced[$key]); //note the 'd', it only triggers onExecuted event
            }

            $this->commit();

            if (isset($transitions[$explicitMachineName]) && $transitions[$explicitMachineName]->isCreating()) {
                $this->logger->log(sprintf(_("Přihláška '%s' vytvořena."), (string)$holder->getPrimaryHolder()->getModel()), ILogger::SUCCESS);
            } else if (isset($transitions[$explicitMachineName]) && $transitions[$explicitMachineName]->isTerminating()) {
                //$this->logger->log(sprintf(_("Přihláška '%s' smazána."), (string) $holder->getPrimaryHolder()->getModel()), ILogger::SUCCESS);
                $this->logger->log(_("Přihláška smazána."), ILogger::SUCCESS);
            } else if (isset($transitions[$explicitMachineName])) {
                $this->logger->log(sprintf(_("Stav přihlášky '%s' změněn."), (string)$holder->getPrimaryHolder()->getModel()), ILogger::INFO);
            }
            if ($data && (!isset($transitions[$explicitMachineName]) || !$transitions[$explicitMachineName]->isTerminating())) {
                $this->logger->log(sprintf(_("Přihláška '%s' uložena."), (string)$holder->getPrimaryHolder()->getModel()), ILogger::SUCCESS);
            }
        } catch (ModelDataConflictException $e) {
            $container = $e->getReferencedId()->getReferencedContainer();
            $container->setConflicts($e->getConflicts());

            $message = sprintf(_("Některá pole skupiny '%s' neodpovídají existujícímu záznamu."), $container->getOption('label'));
            $this->logger->log($message, ILogger::ERROR);
            $this->formRollback($data);
            $this->reraise($e);
        } catch (SecondaryModelDataConflictException $e) {
            $message = sprintf(_("Data ve skupině '%s' kolidují s již existující přihláškou."), $e->getBaseHolder()->getLabel());
            $this->logger->log($message, ILogger::ERROR);
            $this->formRollback($data);
            $this->reraise($e);
        } catch (DuplicateApplicationException $e) {
            $message = $e->getMessage();
            $this->logger->log($message, ILogger::ERROR);
            $this->formRollback($data);
            $this->reraise($e);
        } catch (MachineExecutionException $e) {
            $this->logger->log($e->getMessage(), ILogger::ERROR);
            $this->formRollback($data);
            $this->reraise($e);
        } catch (SubmitProcessingException $e) {
            $this->logger->log($e->getMessage(), ILogger::ERROR);
            $this->formRollback($data);
            $this->reraise($e);
        } catch (StorageException $e) {
            $this->logger->log($e->getMessage(), ILogger::ERROR);
            $this->formRollback($data);
            $this->reraise($e);
        }
    }

    private function processData($data, $transitions, Holder $holder, $execute) {
        if ($data instanceof Form) {
            $values = FormUtils::emptyStrToNull($data->getValues());
            $form = $data;
        } else {
            $values = $data;
            $form = null;
        }

        $primaryName = $holder->getPrimaryHolder()->getName();
        $newStates = [];
        if (isset($values[$primaryName][BaseHolder::STATE_COLUMN])) {
            $newStates[$primaryName] = $values[$primaryName][BaseHolder::STATE_COLUMN];
        }
        // Find out transitions
        $newStates = array_merge($newStates, $holder->processFormValues($values, $this->machine, $transitions, $this->logger, $form));
        if ($execute == self::STATE_TRANSITION) {
            foreach ($newStates as $name => $newState) {
                $transition = $this->machine[$name]->getTransitionByTarget($newState);
                if ($transition) {
                    $transitions[$name] = $transition;
                } elseif (!($this->machine->getBaseMachine($name)->getState() == BaseMachine::STATE_INIT && $newState == BaseMachine::STATE_TERMINATED)) {
                    $msg = _("Ze stavu '%s' automatu '%s' neexistuje přechod do stavu '%s'.");
                    throw new MachineExecutionException(sprintf($msg, $this->machine->getBaseMachine($name)->getStateName(), $holder->getBaseHolder($name)->getLabel(), $this->machine->getBaseMachine($name)->getStateName($newState)));
                }
            }
        }
        return $transitions;
    }

    private function initializeMachine(Holder $holder) {
        if (!$this->machine) {
            $this->machine = $this->container->createEventMachine($this->event);
        }
        if ($this->machine->getHolder() !== $holder) {
            $this->machine->setHolder($holder);
        }
    }

    private function formRollback($data) {
        if ($data instanceof Form) {
            foreach ($data->getComponents(true, 'FKSDB\Components\Forms\Controls\ReferencedId') as $referencedId) {
                $referencedId->rollback();
            }
        }
        $this->rollback();
    }

    public function beginTransaction() {
        if (!$this->connection->inTransaction()) {
            $this->connection->beginTransaction();
        }
    }

    private function rollback() {
        if ($this->errorMode == self::ERROR_ROLLBACK) {
            $this->connection->rollBack();
        }
    }

    public function commit($final = false) {
        if ($this->connection->inTransaction() && ($this->errorMode == self::ERROR_ROLLBACK || $final)) {
            $this->connection->commit();
        }
    }

    private function reRaise(Exception $e) {
        throw new ApplicationHandlerException(_('Chyba při ukládání přihlášky.'), null, $e);
    }

}
