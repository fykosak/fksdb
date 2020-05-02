<?php

namespace Events\Model\Holder;

use ArrayAccess;
use ArrayIterator;
use Events\FormAdjustments\IFormAdjustment;
use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\Machine\Transition;
use Events\Model\Holder\SecondaryModelStrategies\SecondaryModelStrategy;
use Events\Processings\GenKillProcessing;
use Events\Processings\IProcessing;
use FKSDB\Logging\ILogger;
use FKSDB\ORM\IModel;
use IteratorAggregate;
use LogicException;
use Nette\Application\UI\Form;
use Nette\Database\Connection;
use Nette\FreezableObject;
use Nette\InvalidArgumentException;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

/**
 * A bit bloated class.
 *
 * It takes care of data loading/storing and also provides event's metadata.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class Holder extends FreezableObject implements ArrayAccess, IteratorAggregate {

    /**
     * @var IFormAdjustment[]
     */
    private $formAdjustments = [];

    /**
     * @var IProcessing[]
     */
    private $processings = [];

    /**
     * @var BaseHolder[]
     */
    private $baseHolders = [];

    /**
     * @var BaseHolder[]
     */
    private $secondaryBaseHolders = [];

    /**
     * @var BaseHolder
     */
    private $primaryHolder;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Machine
     */
    private $machine;

    /**
     * @var SecondaryModelStrategy
     */
    private $secondaryModelStrategy;

    /**
     * Holder constructor.
     * @param Connection $connection
     */
    function __construct(Connection $connection) {
        $this->connection = $connection;

        /*
         * This implicit processing is the first. It's not optimal
         * and it may be subject to change.
         */
        $this->processings[] = new GenKillProcessing();
    }

    /**
     * @return Connection
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * @param $name
     */
    public function setPrimaryHolder($name) {
        $this->updating();
        $primaryHolder = $this->primaryHolder = $this->getBaseHolder($name);
        $this->secondaryBaseHolders = array_filter($this->baseHolders, function (BaseHolder $baseHolder) use ($primaryHolder) {
            return $baseHolder !== $primaryHolder;
        });
    }

    /**
     * @return BaseHolder
     */
    public function getPrimaryHolder() {
        return $this->primaryHolder;
    }

    /**
     * @param BaseHolder $baseHolder
     */
    public function addBaseHolder(BaseHolder $baseHolder) {
        $this->updating();
        $baseHolder->setHolder($this);
        $baseHolder->freeze();

        $name = $baseHolder->getName();
        $this->baseHolders[$name] = $baseHolder;
    }

    /**
     * @param IFormAdjustment $formAdjusment
     */
    public function addFormAdjustment(IFormAdjustment $formAdjusment) {
        $this->updating();
        $this->formAdjustments[] = $formAdjusment;
    }

    /**
     * @param IProcessing $processing
     */
    public function addProcessing(IProcessing $processing) {
        $this->updating();
        $this->processings[] = $processing;
    }

    /**
     * @param $name
     * @return BaseHolder
     */
    public function getBaseHolder($name) {
        if (!array_key_exists($name, $this->baseHolders)) {
            throw new InvalidArgumentException("Unknown base holder '$name'.");
        }
        return $this->baseHolders[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasBaseHolder($name) {
        return isset($this->baseHolders[$name]);
    }

    /**
     * @deprecated Use getEvent on primary holder explicitly.
     * @return \FKSDB\ORM\Models\ModelEvent
     */
    public function getEvent() {
        return $this->primaryHolder->getEvent();
    }

    /**
     * @return Machine
     */
    public function getMachine() {
        return $this->machine;
    }

    /**
     * @param Machine $machine
     */
    public function setMachine(Machine $machine) {
        $this->machine = $machine;
        if ($machine->getHolder() !== $this) {
            $machine->setHolder($this);
        }
    }

    /**
     * @return SecondaryModelStrategy
     */
    public function getSecondaryModelStrategy() {
        return $this->secondaryModelStrategy;
    }

    /**
     * @param SecondaryModelStrategy $secondaryModelStrategy
     */
    public function setSecondaryModelStrategy(SecondaryModelStrategy $secondaryModelStrategy) {
        $this->updating();
        $this->secondaryModelStrategy = $secondaryModelStrategy;
    }

    /**
     * @param \FKSDB\ORM\IModel|null $primaryModel
     * @param array|null $secondaryModels
     */
    public function setModel(IModel $primaryModel = null, array $secondaryModels = null) {
        foreach ($this->getGroupedSecondaryHolders() as $key => $group) {
            if ($secondaryModels) {
                $this->secondaryModelStrategy->setSecondaryModels($group['holders'], $secondaryModels[$key]);
            } else {
                $this->secondaryModelStrategy->loadSecondaryModels($group['service'], $group['joinOn'], $group['joinTo'], $group['holders'], $primaryModel);
            }
        }
        $this->primaryHolder->setModel($primaryModel);
    }

    public function saveModels() {
        /*
         * When deleting, first delete children, then parent.
         */
        if ($this->primaryHolder->getModelState() == BaseMachine::STATE_TERMINATED) {
            foreach ($this->secondaryBaseHolders as $name => $baseHolder) {
                $baseHolder->saveModel();
            }
            $this->primaryHolder->saveModel();
        } else {
            /*
             * When creating/updating primary model, propagate its PK to referencing secondary models.
             */
            $this->primaryHolder->saveModel();
            $primaryModel = $this->primaryHolder->getModel();

            foreach ($this->getGroupedSecondaryHolders() as $group) {
                $this->secondaryModelStrategy->updateSecondaryModels($group['service'], $group['joinOn'], $group['joinTo'], $group['holders'], $primaryModel);
            }

            foreach ($this->secondaryBaseHolders as $name => $baseHolder) {
                $baseHolder->saveModel();
            }
        }
    }

    /**
     * Apply processings to the values and sets them to the ORM model.
     *
     * @param ArrayHash $values
     * @param Machine $machine
     * @param Transition[] $transitions
     * @param ILogger $logger
     * @param Form $form
     * @return string[] machineName => new state
     */
    public function processFormValues(ArrayHash $values, Machine $machine, $transitions, ILogger $logger, Form $form = null) {
        $newStates = [];
        foreach ($transitions as $name => $transition) {
            $newStates[$name] = $transition->getTarget();
        }
        foreach ($this->processings as $processing) {
            $result = $processing->process($newStates, $values, $machine, $this, $logger, $form);
            if ($result) {
                $newStates = array_merge($newStates, $result);
            }
        }

        foreach ($this->baseHolders as $name => $baseHolder) {
            $stateExist = isset($newStates[$name]);
            if ($stateExist) {
                $alive = ($newStates[$name] != BaseMachine::STATE_TERMINATED);
            } else {
                $alive = true;
            }
            if (isset($values[$name])) {
                $baseHolder->updateModel($values[$name], $alive); // terminated models may not be correctly updated
            }
        }
        return $newStates;
    }

    /**
     * @param Form $form
     * @param Machine $machine
     */
    public function adjustForm(Form $form, Machine $machine) {
        foreach ($this->formAdjustments as $adjustment) {
            $adjustment->adjust($form, $machine, $this);
        }
    }

    /*
     * Joined data manipulation
     */

    private $groupedHolders;

    /**
     * Group secondary by service
     * @return array[] items: joinOn, service, holders
     */
    public function getGroupedSecondaryHolders() {
        if ($this->groupedHolders == null) {
            $this->groupedHolders = [];

            foreach ($this->secondaryBaseHolders as $baseHolder) {
                $key = spl_object_hash($baseHolder->getService());
                if (!isset($this->groupedHolders[$key])) {
                    $this->groupedHolders[$key] = [
                        'joinOn' => $baseHolder->getJoinOn(),
                        'joinTo' => $baseHolder->getJoinTo(),
                        'service' => $baseHolder->getService(),
                        'personIds' => $baseHolder->getPersonIds(),
                        'holders' => [],
                    ];
                }
                $this->groupedHolders[$key]['holders'][] = $baseHolder;
                /*
                 * TODO: Here should be consistency check that all
                 * members of the group have same joinOn, joinTo (and maybe others as wellú
                 */
            }
        }

        return $this->groupedHolders;
    }

    /*
     * Parameters
     */

    /**
     * @param $name
     * @return mixed
     */
    public function getParameter($name) {
        $parts = explode('.', $name, 2);
        if (count($parts) == 1) {
            return $this->primaryHolder->getParameter($name);
        } elseif (isset($this->baseHolders[$parts[0]])) {
            return $this->baseHolders[$parts[0]]->getParameter($parts[1]);
        } else {
            throw new InvalidArgumentException("Invalid parameter '$name' from a base holder.");
        }
    }

    /*
     * Syntax-sugar Interfaces
     */

    /**
     * @return ArrayIterator|\Traversable
     */
    public function getIterator() {
        return new ArrayIterator($this->baseHolders);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->baseHolders[$offset]);
    }

    /**
     * @param mixed $offset
     * @return BaseHolder|mixed
     */
    public function offsetGet($offset) {
        return $this->baseHolders[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value) {
        throw new LogicException('Use addBaseHolder method.');
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset) {
        throw new LogicException('Cannot delete a base holder.');
    }

}
