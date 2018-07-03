<?php

namespace FKSDB\Components\Events;

use Events\Machine\BaseMachine;
use Events\Model\ApplicationHandler;
use Events\Model\ApplicationHandlerException;
use Events\Model\Holder\Holder;
use FKS\Components\Controls\FormControl;
use FKS\Logging\FlashMessageDump;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Callback;
use Nette\Diagnostics\Debugger;
use Nette\Forms\Controls\SubmitButton;
use Nette\InvalidStateException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class ApplicationComponent extends Control {

    /**
     * @var ApplicationHandler
     */
    private $handler;

    /**
     * @var Holder
     */
    private $holder;

    /**
     * @var FlashMessageDump
     */
    private $flashDump;

    /**
     * @var Callback($primaryModelId, $eventId)
     */
    private $redirectCallback;

    /**
     * @var string
     */
    private $templateFile;

    function __construct(ApplicationHandler $handler, Holder $holder, FlashMessageDump $flashDump) {
        parent::__construct();
        $this->handler = $handler;
        $this->holder = $holder;
        $this->flashDump = $flashDump;
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

        $this->template->setFile($this->templateFile);
        $this->template->holder = $this->holder;
        $this->template->event = $this->holder->getEvent();
        $this->template->primaryModel = $this->holder->getPrimaryHolder()->getModel();
        $this->template->primaryMachine = $this->getMachine()->getPrimaryMachine();
        $this->template->render();
    }

    public function renderInline($mode) {
        $this->template->mode = $mode;
        $this->template->holder = $this->holder;
        $this->template->primaryModel = $this->holder->getPrimaryHolder()->getModel();
        $this->template->primaryMachine = $this->getMachine()->getPrimaryMachine();
        $this->template->canEdit = $this->canEdit();

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ApplicationComponent.inline.latte');
        $this->template->render();
    }

    protected function createComponentForm($name) {
        $result = new FormControl();
        $result->setGroupMode(FormControl::GROUP_CONTAINER);
        $form = $result['form'];

        /*
         * Create containers
         */
        Debugger::barDump($this->holder);
        foreach ($this->holder as $name => $baseHolder) {
            $baseMachine = $this->getMachine()->getBaseMachine($name);
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
            $saveSubmit->onClick[] = function (SubmitButton $button) use ($that) {
                $form = $button->getForm();
                $that->handleSubmit($form);
            };
        }
        /*
         * Create transition buttons
         */
        $primaryMachine = $this->getMachine()->getPrimaryMachine();
        $transitionSubmit = null;
        foreach ($primaryMachine->getAvailableTransitions(BaseMachine::EXECUTABLE | BaseMachine::VISIBLE) as $transition) {
            $transitionName = $transition->getName();
            $submit = $form->addSubmit($transitionName, $transition->getLabel());

            $submit->onClick[] = function (SubmitButton $button) use ($transitionName, $that) {
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
        $submit->onClick[] = function (SubmitButton $button) use ($that) {
            $that->finalRedirect();
        };

        /*
         * Custom adjustments
         */
        $this->holder->adjustForm($form, $this->getMachine());
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
        try {
            $this->handler->storeAndExecute($this->holder, $form, $explicitTransitionName, $explicitMachineName);
            $this->flashDump->dump($this->handler->getLogger(), $this->getPresenter());
            $this->finalRedirect();
        } catch (ApplicationHandlerException $e) {
            /* handled elsewhere, here it's to just prevent redirect */
            $this->flashDump->dump($this->handler->getLogger(), $this->getPresenter());
            if (!$form) { // w/out form we don't want to show anything with the same GET params
                $this->finalRedirect();
            }
        }
    }

    /**
     * @return Machine
     */
    private function getMachine() {
        return $this->handler->getMachine($this->holder);
    }

    private function canEdit() {
        return $this->getMachine()->getPrimaryMachine()->getState() != BaseMachine::STATE_INIT && $this->holder->getPrimaryHolder()->isModifiable();
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

