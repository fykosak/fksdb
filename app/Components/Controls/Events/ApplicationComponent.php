<?php

namespace FKSDB\Components\Events;

use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\MachineExecutionException;
use Events\Model\Holder;
use Events\TransitionOnExecutedException;
use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use PublicModule\BasePresenter;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ApplicationComponent extends Control {

    /**
     * @var Machine
     */
    private $machine;

    /**
     * @var Holder
     */
    private $holder;

    function __construct(Machine $machine, Holder $holder) {
        parent::__construct();
        $this->machine = $machine;
        $this->holder = $holder;
    }

    public function renderForm() {
        $this->getComponent('form')->render();
    }

    public function renderInline() {
        echo $this->holder->getPrimaryHolder()->getModelState();
                
//        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ApplicationComponent.inline.latte');
//        $this->template->render();
    }

    protected function createComponentForm($name) {
        $this->initializeMachine();
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer());

        /*
         * Create containers
         */
        foreach ($this->holder as $name => $baseHolder) {
            $baseMachine = $this->machine[$name];
            if (!$baseHolder->isVisible($baseMachine)) {
                continue;
            }
            $container = $baseHolder->createFormContainer($baseMachine);
            $form->addComponent($container, $name);
        }

        /*
         * Create transition buttons
         */
        $primaryMachine = $this->machine->getPrimaryMachine();
        $that = $this;
        foreach ($primaryMachine->getAvailableTransitions() as $transition) {
            $transitionName = $transition->getName();
            $submit = $form->addSubmit($transitionName, $transition->getLabel());

            $submit->onClick[] = function(SubmitButton $button) use($transitionName, $that) {
                        $form = $button->getForm();
                        $that->handleSubmit($form, $transitionName);
                    };
        }

        /*
         * Create save (no transition) button
         */
        //TODO display this button in dependence on modifiable
        if ($primaryMachine->getState() != BaseMachine::STATE_INIT) {
            $submit = $form->addSubmit('save', _('Uložit'));
            $submit->onClick[] = function(SubmitButton $button) use($that) {
                        $form = $button->getForm();
                        $that->handleSubmit($form);
                    };
        }

        return $form;
    }

    public function handleSubmit(Form $form, $explicitTransitionName = null, $explicitMachineName = null) {
        $this->initializeMachine();
        $connection = $this->holder->getConnection();
        try {
            $values = $form->getValues();
            $explicitMachine = $explicitMachineName ? $this->machine[$explicitMachineName] : $this->machine->getPrimaryMachine();

            $connection->beginTransaction();

            /*
             * Find out transitions
             */
            $newStates = $this->holder->processFormValues($values, $this->machine);
            $transitions = array();
            foreach ($newStates as $name => $newState) {
                $transitions[$name] = $this->machine[$name]->getTransitionByTarget($newState);
            }

            if ($explicitTransitionName !== null) {
                if (isset($transitions[$explicitMachineName])) {
                    throw new MachineExecutionException(sprintf('Collision of explicit transision %s and processing transition %s', $explicitTransitionName, $explicitTransitionName[$explicitMachineName]->getName()));
                }
                $transitions[$explicitMachineName] = $explicitMachine->getTransition($explicitTransitionName);
            }

            foreach ($transitions as $transition) {
                try {
                    $transition->execute();
                } catch (TransitionOnExecutedException $e) {
                    $form->addError($e->getMessage()); //TODO rather flash message due to only state change
                }
            }

            $this->holder->saveModels();
            $connection->commit();

            $id = $this->holder->getPrimaryHolder()->getModel()->getPrimary();
            $this->presenter->redirect('this', array(
                'id' => $id,
                'eventId' => $this->holder->getEvent()->getPrimary()
            ));
        } catch (Exception $e) {
            $this->presenter->flashMessage($e->getMessage(), BasePresenter::FLASH_ERROR);
            $connection->rollBack();
        }
        /* catch (TransitionConditionFailedException $e) {
          $form->addError($e->getMessage());
          $connection->rollBack();
          } catch (SubmitProcessingException $e) {
          $form->addError($e->getMessage());
          $connection->rollBack();
          } */
    }

    private function initializeMachine() {
        $this->machine->setHolder($this->holder);
    }

}
