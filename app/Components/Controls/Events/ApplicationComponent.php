<?php

namespace FKSDB\Components\Events;

use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\MachineExecutionException;
use Events\Model\Holder\Holder;
use Events\SubmitProcessingException;
use Events\TransitionOnExecutedException;
use FKS\Components\Controls\FormControl;
use FKS\Components\Forms\Controls\ModelDataConflictException;
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

    public function setRedirectCallback($redirectCallback) {
        $this->redirectCallback = new Callback($redirectCallback);
    }

    protected function createTemplate($class = NULL) {
        $template = parent::createTemplate($class);
        $template->setTranslator($this->presenter->getTranslator());
        return $template;
    }

    public function render() {
        $this->renderForm();
    }

    public function renderForm() {
        $this->initializeMachine();

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ApplicationComponent.form.latte');
        $this->template->render();
    }

    public function renderInline($mode) {
        $this->initializeMachine();

        $this->template->mode = $mode;
        $this->template->holder = $this->holder;
        $this->template->primaryModel = $this->holder->getPrimaryHolder()->getModel();
        $this->template->primaryMachine = $this->machine->getPrimaryMachine();
        $this->template->canEdit = $this->canEdit();

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ApplicationComponent.inline.latte');
        $this->template->render();
    }

    protected function createComponentForm($name) {
        $this->initializeMachine();
        $result = new FormControl();
        $result->setGroupMode(FormControl::GROUP_CONTAINER);
        $form = $result['form'];

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

        $that = $this;
        /*
         * Create save (no transition) button
         */
        if ($this->canEdit()) {
            $submit = $form->addSubmit('save', _('Uložit'));
            $submit->setOption('row', 1);
            $submit->onClick[] = function(SubmitButton $button) use($that) {
                        $form = $button->getForm();
                        $that->handleSubmit($form);
                    };
        }
        /*
         * Create transition buttons
         */
        $primaryMachine = $this->machine->getPrimaryMachine();
        foreach ($primaryMachine->getAvailableTransitions() as $transition) {
            $transitionName = $transition->getName();
            $submit = $form->addSubmit($transitionName, $transition->getLabel());

            $submit->onClick[] = function(SubmitButton $button) use($transitionName, $that) {
                        $form = $button->getForm();
                        $that->handleSubmit($form, $transitionName);
                    };

            if ($transition->isCreating()) {
                $submit->getControlPrototype()->addClass('btn-success');
                $submit->setOption('row', 1);
            } else {
                $submit->getControlPrototype()->addClass('btn-default');
                $submit->setOption('row', 2);
            }
        }

        /*
         * Create cancel button
         */
        $submit = $form->addSubmit('cancel', _('Storno'));
        $submit->setOption('row', 1);
        $submit->setValidationScope(false);
        $submit->getControlPrototype()->addClass('btn-link');
        $submit->onClick[] = function(SubmitButton $button) use($that) {
                    $that->initializeMachine();
                    $that->finalRedirect();
                };

        /*
         * Custom adjustments
         */
        $this->holder->adjustForm($form, $this->machine);

        return $result;
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
                $transition->execute();
            }

            $this->holder->saveModels();

            foreach ($transitions as $transition) {
                $transition->executed(); //note the 'd', it only triggers onExecuted event
            }

            $connection->commit();

            if ($form) {
                $this->presenter->flashMessage(sprintf(_("Přihláška '%s' uložena."), (string) $this->holder->getPrimaryHolder()->getModel()), BasePresenter::FLASH_SUCCESS);
            }
            if (count($transitions) == 1 && reset($transitions)->isCreating()) {
                $this->presenter->flashMessage(sprintf(_("Přihláška '%s' vytvořena."), (string) $this->holder->getPrimaryHolder()->getModel()), BasePresenter::FLASH_SUCCESS);
            } else if (isset($transitions[$explicitMachineName])) {
                $this->presenter->flashMessage(sprintf(_("Stav přihlášky '%s' změněn."), (string) $this->holder->getPrimaryHolder()->getModel()), BasePresenter::FLASH_INFO);
            }

            $this->finalRedirect();
        } catch (ModelDataConflictException $e) {
            $container = $e->getReferencedId()->getReferencedContainer();
            $container->setConflicts($e->getConflicts());

            $message = sprintf(_("Některá pole skupiny '%s' neodpovídají existujícímu záznamu."), $container->getOption('label'));
            $this->presenter->flashMessage($message, BasePresenter::FLASH_ERROR);

            $connection->rollBack();
        } catch (TransitionOnExecutedException $e) {
            $this->presenter->flashMessage($e->getMessage(), BasePresenter::FLASH_ERROR);
            $connection->rollBack();
        } catch (SubmitProcessingException $e) {
            $this->presenter->flashMessage($e->getMessage(), BasePresenter::FLASH_ERROR);
            $connection->rollBack();
        }
    }

    private function processValues(Form $form) {
        $values = $form->getValues();
        // Find out transitions
        $newStates = $this->holder->processFormValues($this->getPresenter(), $values, $this->machine);
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

    private function finalRedirect() {
        if ($this->redirectCallback) {
            $id = $this->holder->getPrimaryHolder()->getModel()->getPrimary(false);
            $this->redirectCallback->invoke($id, $this->holder->getEvent()->getPrimary());
        } else {
            $this->redirect('this');
        }
    }

}

