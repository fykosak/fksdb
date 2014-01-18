<?php

namespace Events\Model\Holder;

use ArrayAccess;
use ArrayIterator;
use Events\FormAdjustments\IFormAdjustment;
use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\Processings\GenKillProcessing;
use Events\Processings\IProcessing;
use FKS\Config\NeonScheme;
use IteratorAggregate;
use LogicException;
use ModelEvent;
use Nette\ArrayHash;
use Nette\Database\Connection;
use Nette\Forms\Form;
use Nette\FreezableObject;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\Utils\Neon;
use ORM\IModel;
use ORM\IService;

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
    private $formAdjustments = array();

    /**
     * @var IProcessing[]
     */
    private $processings = array();

    /**
     * @var BaseHolder[]
     */
    private $baseHolders = array();

    /**
     * @var BaseHolder[]
     */
    private $secondaryBaseHolders = array();

    /**
     * @var BaseHolder
     */
    private $primaryHolder;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * @var array
     */
    private $paramScheme;

    /**
     * @var array
     */
    private $parameters;

    function __construct(Connection $connection) {
        $this->connection = $connection;

        /*
         * This implicit processing is the first. It's not optimal
         * and it may be subject to change.
         */
        $this->processings[] = new GenKillProcessing();
    }

    public function getConnection() {
        return $this->connection;
    }

    public function setPrimaryHolder($name) {
        $this->updating();
        $primaryHolder = $this->primaryHolder = $this->getBaseHolder($name);
        $this->secondaryBaseHolders = array_filter($this->baseHolders, function(BaseHolder $baseHolder) use($primaryHolder) {
                    return $baseHolder !== $primaryHolder;
                });
    }

    public function getPrimaryHolder() {
        return $this->primaryHolder;
    }

    public function addBaseHolder(BaseHolder $baseHolder) {
        $this->updating();
        $baseHolder->setHolder($this);
        $baseHolder->freeze();

        $name = $baseHolder->getName();
        $this->baseHolders[$name] = $baseHolder;
    }

    public function addFormAdjustment(IFormAdjustment $formAdjusment) {
        $this->updating();
        $this->formAdjustments[] = $formAdjusment;
    }

    public function addProcessing(IProcessing $processing) {
        $this->updating();
        $this->processings[] = $processing;
    }

    public function getBaseHolder($name) {
        if (!array_key_exists($name, $this->baseHolders)) {
            throw new InvalidArgumentException("Unknown base holder '$name'.");
        }
        return $this->baseHolders[$name];
    }

    public function getEvent() {
        return $this->event;
    }

    public function setEvent(ModelEvent $event) {
        $this->updating();
        $this->event = $event;
        $this->cacheParameters();
    }

    public function getParamScheme() {
        return $this->paramScheme;
    }

    public function setParamScheme($paramScheme) {
        $this->updating();
        $this->paramScheme = $paramScheme;
    }

    public function setModel(IModel $primaryModel = null, array $secondaryModels = null) {
        foreach ($this->getGroupedSecondaryHolders() as $key => $group) {
            if ($secondaryModels) {
                $this->setSecondaryModels($group['holders'], $secondaryModels[$key]);
            } else {
                $this->loadSecondaryModels($group['service'], $group['joinOn'], $group['holders'], $primaryModel);
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
             * When creating/updating primary model, propagate its PK to referencinf secondary models.
             */
            $this->primaryHolder->saveModel();
            $primaryModel = $this->primaryHolder->getModel();

            foreach ($this->getGroupedSecondaryHolders() as $group) {
                $this->updateSecondaryModels($group['service'], $group['joinOn'], $group['holders'], $primaryModel);
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
     * * @param \Events\Model\Machine $machine
     * @return string[] machineName => new state
     */
    public function processFormValues(ArrayHash $values, Machine $machine) {
        $newStates = array();
        foreach ($this->processings as $processing) {
            $result = $processing->process($values, $machine, $this);
            if ($result) {
                $newStates = array_merge($newStates, $result);
            }
        }

        foreach ($this->baseHolders as $name => $baseHolder) {
            if (isset($values[$name])) {
                $baseHolder->updateModel($values[$name]);
            }
        }
        return $newStates;
    }

    public function adjustForm(Form $form, Machine $machine) {
        foreach ($this->formAdjustments as $adjustment) {
            $adjustment->adjust($form, $machine, $this);
        }
    }

    /*
     * Joined data manipulation
     */

    /**
     * Group secondary by service
     * @return array[] items: joinOn, service, holders
     */
    public function getGroupedSecondaryHolders() {
        static $result = null; // cache

        if ($result == null) {
            $result = array();

            foreach ($this->secondaryBaseHolders as $baseHolder) {
                $key = spl_object_hash($baseHolder->getService());
                if (!isset($result[$key])) {
                    $result[$key] = array(
                        'joinOn' => $baseHolder->getJoinOn(),
                        'service' => $baseHolder->getService(),
                        'personIds' => $baseHolder->getPersonIds(),
                        'holders' => array(),
                    );
                }
                $result[$key]['holders'][] = $baseHolder;
            }
        }

        return $result;
    }

    private function setSecondaryModels($holders, $models) {
        $filledHandlers = 0;
        foreach ($models as $secondaryModel) {
            $holders[$filledHandlers]->setModel($secondaryModel);
            if (++$filledHandlers >= count($holders)) {
                throw new InvalidStateException('More than expected secondary models supplied.');
            }
        }
        for (; $filledHandlers < count($holders); ++$filledHandlers) {
            $holders[$filledHandlers]->setModel(null);
        }
    }

    private function loadSecondaryModels(IService $service, $joinOn, $holders, IModel $primaryModel = null) {
        $table = $service->getTable();
        $secondary = $primaryModel ? $table->where($joinOn, $primaryModel->getPrimary()) : array();
        $this->setSecondaryModels($holders, $secondary);
    }

    private function updateSecondaryModels(IService $service, $joinOn, $holders, IModel $primaryModel = null) {
        foreach ($holders as $holder) {
            $service->updateModel($holder->getModel(), array($joinOn => $primaryModel->getPrimary()));
        }
    }

    /*
     * Parameter handling
     */

    private function cacheParameters() {
        $parameters = $this->getEvent()->parameters;
        $parameters = Neon::decode($parameters);
        $this->parameters = $parameters ? NeonScheme::readSection($parameters, $this->getParamScheme()) : array();
    }

    public function getParameter($name, $default = null) {
        $args = func_get_args();
        array_unshift($args, $this->parameters);
        return call_user_func_array('Arrays::get', $args);
    }

    /*
     * Syntax-sugar Interfaces
     */

    public function getIterator() {
        return new ArrayIterator($this->baseHolders);
    }

    public function offsetExists($offset) {
        return isset($this->baseHolders[$offset]);
    }

    public function offsetGet($offset) {
        return $this->baseHolders[$offset];
    }

    public function offsetSet($offset, $value) {
        throw new LogicException('Use addBaseHolder method.');
    }

    public function offsetUnset($offset) {
        throw new LogicException('Cannot delete a base holder.');
    }

}

