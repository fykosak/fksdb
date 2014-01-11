<?php

namespace Events\Machine;

use ArrayAccess;
use Events\SaveHandler;
use IteratorAggregate;
use Nette\Application\UI\Form;
use Nette\FreezableObject;
use Nette\InvalidArgumentException;
use RuntimeException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class Machine extends FreezableObject implements ArrayAccess, IteratorAggregate {

    /**
     * @var array of function(ArrayHash $values, Machine $machine)
     */
    public $onSubmit;

    /**
     * @var BaseMachine[]
     */
    private $baseMachines = array();

    /**
     * @var BaseMachine
     */
    private $primaryMachine;

    /**
     *
     * @var SaveHandler
     */
    private $handler;

    public function setPrimaryMachine($name) {
        $this->updating();
        $this->primaryMachine = $this->getBaseMachine($name);
    }

    public function getPrimaryMachine() {
        return $this->primaryMachine;
    }

    public function addBaseMachine(BaseMachine $baseMachine) {
        $this->updating();
        $name = $baseMachine->getName();
        $this->baseMachines[$name] = $baseMachine;

        $baseMachine->setMachine($this);
        $baseMachine->freeze();
    }

    public function setHandler(SaveHandler $handler) {
        $this->updating();
        $this->handler = $handler;
    }

    public function getHandler() {
        return $this->handler;
    }

    public function getBaseMachine($name) {
        if (!array_key_exists($name, $this->baseMachines)) {
            throw new InvalidArgumentException("Unknown base machine '$name'.");
        }
        return $this->baseMachines[$name];
    }

    public function createForm() {
        $form = new Form();

        // create fields for each base machine
        foreach ($this->baseMachines as $name => $baseMachine) {
            $container = $baseMachine->createFormContainer();
            $form->addComponent($container, $name);
        }

        // create transition buttons
        $that = $this;
        foreach ($this->primaryMachine->getAvailableTransitions() as $transition) {
            $transitionName = $transition->getName();
            $submit = $form->addSubmit($transitionName, $transition->getLabel());
            $submit->onClick[] = function(Form $form) use($transitionName, $that) {
                        $that->handleSubmit($form, $transitionName);
                    };
        }

        // create save (no transition) button
        if ($this->primaryMachine->getState() != BaseMachine::STATE_INIT) {
            $submit = $form->addSubmit('save', _('Uložit'));
            $submit->onClick[] = array($this, 'handleSubmit');
        }

        return $form;
    }

    private function handleSubmit(Form $form, $transitionName = null) {
        try {
            $values = $form->getValues();
            $this->onSubmit($values, $this); //TODO check this can modify data by reference

            if ($transitionName !== null) {
                $transition = $this->primaryMachine->getTransition($transitionName);
                $transition->execute(); //TODO may need some parameters
            }

            $this->getHandler()->save($values, $this);
        } catch (TransitionConditionFailedException $e) {
            $form->addError($e->getMessage());
        } catch (SubmitProcessingException $e) {
            $form->addError($e->getMessage());
        }
    }

    public function getIterator() {
        return new ArrayIterator($this->baseMachines);
    }

    public function offsetExists($offset) {
        return isset($this->baseMachines[$offset]);
    }

    public function offsetGet($offset) {
        return $this->baseMachines[$offset];
    }

    public function offsetSet($offset, $value) {
        throw new LogicException('Use addBaseMachine method.');
    }

    public function offsetUnset($offset) {
        throw new LogicException('Cannot delete a base machine.');
    }

    /*
     * Syntacitc-sugar interfaces
     */
}

