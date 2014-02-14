<?php

namespace FKSDB\Components\Events;

use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\MachineExecutionException;
use Events\Model\Holder\Holder;
use Events\SubmitProcessingException;
use FKS\Components\Controls\FormControl;
use FKS\Components\Forms\Controls\ModelDataConflictException;
use FormUtils;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Callback;
use Nette\Forms\Controls\SubmitButton;
use Nette\InvalidStateException;
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

    /**
     * @var string
     */
    private $templateFile;

    function __construct(Machine $machine, Holder $holder) {
        parent::__construct();
        $this->machine = $machine;
        $this->holder = $holder;
    }

    /**
     * @param string $template name of the standard template or whole path
     */
    public function setTemplate($template) {
        if (stripos($template, '.latte') !== false) {
            $this->templateFile = $template;
        } else {
            $this->templateFile = __DIR__ . DIRECTORY_SEPARATOR . "ApplicationComponent.$template.latte";
        }
    }

    public function getRedirectCallback() {
        return $this->redirectCallback;
    }

    public function setRedirectCallback($redirectCallback) {
        $this->redirectCallback = new Callback($redirectCallback);
    }

    /**
     * Syntactic sugar for the template.
     */
    public function isEventAdmin() {
        $event = $this->holder->getEvent();
        return $this->getPresenter()->getContestAuthorizator()->isAllowed($event, 'application', $event->getContest());
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
        if (!$this->templateFile) {
            throw new InvalidStateException('Must set template for the application form.');
        }

        $this->initializeMachine();

        $this->template->setFile($this->templateFile);
        $this->template->holder = $this->holder;
        $this->template->event = $this->holder->getEvent();
        $this->template->primaryModel = $this->holder->getPrimaryHolder()->getModel();
        $this->template->primaryMachine = $this->machine->getPrimaryMachine();
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
        $saveSubmit = null;
        if ($this->canEdit()) {
            $saveSubmit = $form->addSubmit('save', _('Uložit'));
            $saveSubmit->setOption('row', 1);
            $saveSubmit->onClick[] = function(SubmitButton $button) use($that) {
                        $form = $button->getForm();
                        $that->handleSubmit($form);
                    };
        }
        /*
         * Create transition buttons
         */
        $primaryMachine = $this->machine->getPrimaryMachine();
        $transitionSubmit = null;
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
                if ($transitionSubmit !== false) {
                    $transitionSubmit = $submit;
                } else if ($transitionSubmit) {
                    $transitionSubmit = false; // if there is more than one submit set no one
                }
            } else if ($transition->isTerminating()) {
                $submit->getControlPrototype()->addClass('btn-danger');
                $submit->setOption('row', 3);
            } else if ($transition->isDangerous()) {
                $submit->getControlPrototype()->addClass('btn-danger');
                $submit->setOption('row', 2);
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
        $form->getElementPrototype()->data['submit-on'] = 'enter';
        if ($saveSubmit) {
            $saveSubmit->getControlPrototype()->data['submit-on'] = 'this';
        } else if ($transitionSubmit) {
            $transitionSubmit->getControlPrototype()->data['submit-on'] = 'this';
        }

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
            $explicitMachineName = $explicitMachineName ? : $this->machine->getPrimaryMachine()->getName();

            $connection->beginTransaction();

            $transitions = array();
            if ($explicitTransitionName !== null) {
                $explicitMachine = $this->machine[$explicitMachineName];
                $explicitTransition = $explicitMachine->getTransition($explicitTransitionName);

                $transitions[$explicitMachineName] = $explicitTransition;
            }

            if ($form) {
                $transitions = $this->processValues($form, $transitions);
            }


            foreach ($transitions as $transition) {
                $transition->execute();
            }

            $this->holder->saveModels();

            foreach ($transitions as $transition) {
                $transition->executed(); //note the 'd', it only triggers onExecuted event
            }

            $connection->commit();

            if (isset($transitions[$explicitMachineName]) && $transitions[$explicitMachineName]->isCreating()) {
                $this->presenter->flashMessage(sprintf(_("Přihláška '%s' vytvořena."), (string) $this->holder->getPrimaryHolder()->getModel()), BasePresenter::FLASH_SUCCESS);
            } else if (isset($transitions[$explicitMachineName]) && $transitions[$explicitMachineName]->isTerminating()) {
                $this->presenter->flashMessage(sprintf(_("Přihláška '%s' smazána."), (string) $this->holder->getPrimaryHolder()->getModel()), BasePresenter::FLASH_SUCCESS);
            } else if (isset($transitions[$explicitMachineName])) {
                $this->presenter->flashMessage(sprintf(_("Stav přihlášky '%s' změněn."), (string) $this->holder->getPrimaryHolder()->getModel()), BasePresenter::FLASH_INFO);
            }
            if ($form && (!isset($transitions[$explicitMachineName]) || !$transitions[$explicitMachineName]->isTerminating())) {
                $this->presenter->flashMessage(sprintf(_("Přihláška '%s' uložena."), (string) $this->holder->getPrimaryHolder()->getModel()), BasePresenter::FLASH_SUCCESS);
            }


            $this->finalRedirect();
        } catch (ModelDataConflictException $e) {
            $container = $e->getReferencedId()->getReferencedContainer();
            $container->setConflicts($e->getConflicts());

            $message = sprintf(_("Některá pole skupiny '%s' neodpovídají existujícímu záznamu."), $container->getOption('label'));
            $this->presenter->flashMessage($message, BasePresenter::FLASH_ERROR);

            if ($form) {
                $this->rollbackReferencedId($form);
            }
            $connection->rollBack();
        } catch (MachineExecutionException $e) {
            $this->presenter->flashMessage($e->getMessage(), BasePresenter::FLASH_ERROR);
            if ($form) {
                $this->rollbackReferencedId($form);
            }
            $connection->rollBack();
        } catch (SubmitProcessingException $e) {
            $this->presenter->flashMessage($e->getMessage(), BasePresenter::FLASH_ERROR);
            if ($form) {
                $this->rollbackReferencedId($form);
            }
            $connection->rollBack();
        }
    }

    private function processValues(Form $form, $transitions) {
        $values = FormUtils::emptyStrToNull($form->getValues());

        // Find out transitions
        $newStates = $this->holder->processFormValues($form, $values, $this->machine, $transitions);
        foreach ($newStates as $name => $newState) {
            $transition = $this->machine[$name]->getTransitionByTarget($newState);
            if ($transition) {
                $transitions[$name] = $transition;
            } elseif (!($this->machine->getBaseMachine($name)->getState() == BaseMachine::STATE_INIT && $newState == BaseMachine::STATE_TERMINATED)) {
                $msg = _("Ze stavu automatu '%s' neexistuje přechod do stavu '%s'.");
                throw new MachineExecutionException(sprintf($msg, $this->holder->getBaseHolder($name)->getLabel(), $this->machine->getBaseMachine($name)->getStateName($newState)));
            }
        }
        return $transitions;
    }

    private function rollbackReferencedId(Form $form) {
        foreach ($form->getComponents(true, 'FKS\Components\Forms\Controls\ReferencedId') as $referencedId) {
            $referencedId->rollback();
        }
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

