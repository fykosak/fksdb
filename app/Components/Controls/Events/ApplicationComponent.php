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
use Nette\Callback;
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

    /**
     * @var Callback($primaryModelId, $eventId)
     */
    private $redirectCallback;

    function __construct(Machine $machine, Holder $holder) {
        parent::__construct();
        $this->machine = $machine;
        $this->holder = $holder;
    }

    public function getRedirectCallback() {
        return $this->redirectCallback;
    }

    public function setRedirectCallback(Callback $redirectCallback) {
        $this->redirectCallback = $redirectCallback;
    }

    protected function createTemplate($class = NULL) {
        $template = parent::createTemplate($class);
        $template->setTranslator($this->presenter->getTranslator());
        return $template;
    }

    public function renderForm() {
        $this->getComponent('form')->render();
    }

    public function renderInline($mode) {
        $this->initializeMachine();
        if ($mode == 'state') {
            echo $this->machine->getPrimaryMachine()->getStateName();
        } else if ($mode == 'transitions') {
            $primaryMachine = $this->machine->getPrimaryMachine();
            $this->template->availableTransitions = $primaryMachine->getAvailableTransitions();
            $this->template->primaryModel = $this->holder->getPrimaryHolder()->getModel();
            $this->template->canEdit = $this->canEdit();

            $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ApplicationComponent.inline.latte');
            $this->template->render();
        }
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
        if ($this->canEdit()) {
            $submit = $form->addSubmit('save', _('Uložit'));
            $submit->onClick[] = function(SubmitButton $button) use($that) {
                        $form = $button->getForm();
                        $that->handleSubmit($form);
                    };
        }

        return $form;
    }

    public function handleSubmit(Form $form, $explicitTransitionName = null, $explicitMachineName = null) {
        $this->execute($form, $explicitTransitionName, $explicitMachineName);
    }

    public function handleTransition($transitionName) {
        $this->execute(null, $transitionName);
    }

    private function execute(Form $form = null, $explicitTransitionName = null, $explicitMachineName = null) {
        $this->initializeMachine();
        $connection = $this->holder->getConnection();
        try {
            $explicitMachine = $explicitMachineName ? $this->machine[$explicitMachineName] : $this->machine->getPrimaryMachine();

            $connection->beginTransaction();

            $transitions = array();
            if ($form) {
                $transitions = $this->processValues($form);
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
                    $this->presenter->flashMessage($e->getMessage(), BasePresenter::FLASH_ERROR);
                }
            }

            $this->holder->saveModels();
            $connection->commit();

            if ($this->redirectCallback) {
                $id = $this->holder->getPrimaryHolder()->getModel()->getPrimary();
                $this->redirectCallback->invoke($id, $this->holder->getEvent()->getPrimary());
            } else {
                $this->redirect('this');
            }
        } catch (Exception $e) {
            $this->presenter->flashMessage($e->getMessage(), BasePresenter::FLASH_ERROR);
            $connection->rollBack();
        }
    }

    private function processValues(Form $form) {
        $values = $form->getValues();
        // Find out transitions
        $newStates = $this->holder->processFormValues($values, $this->machine);
        $transitions = array();
        foreach ($newStates as $name => $newState) {
            $transitions[$name] = $this->machine[$name]->getTransitionByTarget($newState);
        }
        return $transitions;
    }

    private function initializeMachine() {
        $this->machine->setHolder($this->holder);
    }

    private function canEdit() {
        //TODO display this button in dependence on modifiable
        return $this->machine->getPrimaryMachine()->getState() != BaseMachine::STATE_INIT;
    }

}

