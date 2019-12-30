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
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Controls\Schedule\ExistingPaymentException;
use FKSDB\Components\Forms\Controls\Schedule\FullCapacityException;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\Models\ModelEvent;
use FormUtils;
use Nette\Database\Connection;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;

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
     * @param \FKSDB\ORM\Models\ModelEvent $event
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
     */
    public final function store(Holder $holder, $data) {
        $this->_storeAndExecute($holder, $data, null, self::STATE_OVERWRITE);
    }

    /**
     * @param Holder $holder
     * @param Form|ArrayHash|null $data
     * @param mixed $explicitTransitionName
     */
    public function storeAndExecute(Holder $holder, $data = null, $explicitTransitionName = null) {
        $this->_storeAndExecute($holder, $data, $explicitTransitionName, self::STATE_TRANSITION);
    }

    /**
     * @param Holder $holder
     * @param $data
     * @param $explicitTransitionName
     * @param $execute
     */
    private function _storeAndExecute(Holder $holder, $data, $explicitTransitionName, $execute) {
        $this->initializeMachine($holder);

        try {
            $explicitMachineName = $this->machine->getPrimaryMachine()->getName();

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
                $this->logger->log(sprintf(_('Přihláška "%s" vytvořena.'), (string)$holder->getPrimaryHolder()->getModel()), ILogger::SUCCESS);
            } else if (isset($transitions[$explicitMachineName]) && $transitions[$explicitMachineName]->isTerminating()) {
                //$this->logger->log(sprintf(_("Přihláška '%s' smazána."), (string) $holder->getPrimaryHolder()->getModel()), ILogger::SUCCESS);
                $this->logger->log(_('Přihláška smazána.'), ILogger::SUCCESS);
            } else if (isset($transitions[$explicitMachineName])) {
                $this->logger->log(sprintf(_('Stav přihlášky "%s" změněn.'), (string)$holder->getPrimaryHolder()->getModel()), ILogger::INFO);
            }
            if ($data && (!isset($transitions[$explicitMachineName]) || !$transitions[$explicitMachineName]->isTerminating())) {
                $this->logger->log(sprintf(_('Přihláška "%s" uložena.'), (string)$holder->getPrimaryHolder()->getModel()), ILogger::SUCCESS);
            }
        } catch (ModelDataConflictException $exception) {
            $container = $exception->getReferencedId()->getReferencedContainer();
            $container->setConflicts($exception->getConflicts());

            $message = sprintf(_('Některá pole skupiny "%s" neodpovídají existujícímu záznamu.'), $container->getOption('label'));
            $this->logger->log($message, ILogger::ERROR);
            $this->formRollback($data);
            $this->reRaise($exception);
        } catch (SecondaryModelDataConflictException $exception) {
            $message = sprintf(_('Data ve skupině "%s" kolidují s již existující přihláškou.'), $exception->getBaseHolder()->getLabel());
            $this->logger->log($message, ILogger::ERROR);
            $this->formRollback($data);
            $this->reRaise($exception);
        } catch (DuplicateApplicationException $exception) {
            $message = $exception->getMessage();
            $this->logger->log($message, ILogger::ERROR);
            $this->formRollback($data);
            $this->reRaise($exception);
        } catch (MachineExecutionException $exception) {
            $this->logger->log($exception->getMessage(), ILogger::ERROR);
            $this->formRollback($data);
            $this->reRaise($exception);
        } catch (SubmitProcessingException $exception) {
            $this->logger->log($exception->getMessage(), ILogger::ERROR);
            $this->formRollback($data);
            $this->reRaise($exception);
        } catch (FullCapacityException $exception) {
            $this->logger->log($exception->getMessage(), ILogger::ERROR);
            $this->formRollback($data);
            $this->reRaise($exception);
        } catch (ExistingPaymentException $exception) {
            $this->logger->log($exception->getMessage(), ILogger::ERROR);
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
     */
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
                    $msg = _('Ze stavu "%s" automatu "%s" neexistuje přechod do stavu "%s".');
                    throw new MachineExecutionException(sprintf($msg, $this->machine->getBaseMachine($name)->getStateName(), $holder->getBaseHolder($name)->getLabel(), $this->machine->getBaseMachine($name)->getStateName($newState)));
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
        if ($this->machine->getHolder() !== $holder) {
            $this->machine->setHolder($holder);
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
        if (!$this->connection->inTransaction()) {
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
        if ($this->connection->inTransaction() && ($this->errorMode == self::ERROR_ROLLBACK || $final)) {
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
