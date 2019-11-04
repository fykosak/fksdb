<?php

namespace FKSDB\Components\Events;

use Events\Machine\BaseMachine;
use Events\Machine\Machine;
use Events\Machine\Transition;
use Events\Model\ApplicationHandler;
use Events\Model\ApplicationHandlerException;
use Events\Model\Holder\Holder;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Logging\FlashMessageDump;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\InvalidStateException;
use Nette\Templating\FileTemplate;
use Nette\Templating\ITemplate;
use Tracy\Debugger;

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
     * @var callable ($primaryModelId, $eventId)
     */
    private $redirectCallback;

    /**
     * @var string
     */
    private $templateFile;

    /**
     * ApplicationComponent constructor.
     * @param ApplicationHandler $handler
     * @param Holder $holder
     * @param FlashMessageDump $flashDump
     */
    function __construct(ApplicationHandler $handler, Holder $holder, FlashMessageDump $flashDump) {
        parent::__construct();
        $this->handler = $handler;
        $this->holder = $holder;
        $this->flashDump = $flashDump;
    }

    /**
     * @param "form"|"inline" $template name of the standard template or whole path
     */
    public function setTemplate($template) {
        if (stripos($template, '.latte') !== false) {
            $this->templateFile = $template;
        } else {
            $this->templateFile = __DIR__ . DIRECTORY_SEPARATOR . "ApplicationComponent.$template.latte";
        }
    }

    /**
     * @return callable
     */
    public function getRedirectCallback() {
        return $this->redirectCallback;
    }

    /**
     * @param $redirectCallback
     */
    public function setRedirectCallback(callable $redirectCallback) {
        $this->redirectCallback = $redirectCallback;
    }

    /**
     * Syntactic sugar for the template.
     */
    public function isEventAdmin() {
        $event = $this->holder->getPrimaryHolder()->getEvent();
        return $this->getPresenter()->getContestAuthorizator()->isAllowed($event, 'application', $event->getContest());
    }

    /**
     * @param null $class
     * @return FileTemplate|ITemplate
     */
    protected function createTemplate($class = NULL) {
        /**
         * @var FileTemplate $template
         */
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

    /**
     * @param $mode
     */
    public function renderInline($mode) {
        $this->template->mode = $mode;
        $this->template->holder = $this->holder;
        $this->template->primaryModel = $this->holder->getPrimaryHolder()->getModel();
        $this->template->primaryMachine = $this->getMachine()->getPrimaryMachine();
        $this->template->canEdit = $this->canEdit();

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ApplicationComponent.inline.latte');
        $this->template->render();
    }

    /**
     * @return FormControl
     * @throws BadRequestException
     */
    protected function createComponentForm() {
        $result = new FormControl();
        $form = $result->getForm();

        /*
         * Create containers
         */
        foreach ($this->holder as $name => $baseHolder) {
            $baseMachine = $this->getMachine()->getBaseMachine($name);
            if (!$baseHolder->isVisible($baseMachine)) {
                continue;
            }
            $container = $baseHolder->createFormContainer($baseMachine);
            $form->addComponent($container, $name);
        }

        /*
         * Create save (no transition) button
         */
        $saveSubmit = null;
        if ($this->canEdit()) {
            $saveSubmit = $form->addSubmit('save', _('Uložit'));
        }
        /*
         * Create transition buttons
         */
        $primaryMachine = $this->getMachine()->getPrimaryMachine();
        $transitionSubmit = null;
        /**
         * @var Transition $transition
         */
        foreach ($primaryMachine->getAvailableTransitions(BaseMachine::EXECUTABLE | BaseMachine::VISIBLE) as $transition) {
            $transitionName = $transition->getName();
            $submit = $form->addSubmit($transitionName, $transition->getLabel());

            if ($transition->isCreating()) {
                $submit->getControlPrototype()->addClass('btn-sm btn-success');
                if ($transitionSubmit !== false) {
                    $transitionSubmit = $submit;
                } else if ($transitionSubmit) {
                    $transitionSubmit = false; // if there is more than one submit set no one
                }
            } else if ($transition->isTerminating()) {
                $submit->getControlPrototype()->addClass('btn-sm btn-danger');
            } else if ($transition->isDangerous()) {
                $submit->getControlPrototype()->addClass('btn-sm btn-danger');
            } else {
                $submit->getControlPrototype()->addClass('btn-sm btn-secondary');
            }
        }

        /*
         * Create cancel button
         */
        $submit = $form->addSubmit('cancel', _('Cancel'));
        $submit->setValidationScope(false);
        $submit->getControlPrototype()->addClass('btn-warning');
        $submit->onClick[] = function (SubmitButton $button) {
            $this->finalRedirect();
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
        $form->onSuccess[] = function () use ($form) {
            $this->handleSuccess($form);
        };

        return $result;
    }

    /**
     * @param Form $form
     * @throws AbortException
     */
    private function handleSuccess(Form $form) {
        foreach ($form->getControls() as $control) {
            if ($control instanceof SubmitButton) {
                if ($control->isSubmittedBy()) {
                    if (preg_match('/.*__.*/', $control->getName())) {
                        Debugger::barDump($control->getName());
                        $this->execute($form, $control->getName());
                    } else {
                        $this->execute($form, null);
                    }
                }
            }
        }
    }

    /**
     * @param Form|null $form
     * @param null $explicitTransitionName
     * @throws AbortException
     */
    private function execute(Form $form = null, $explicitTransitionName = null) {
        try {
            $this->handler->storeAndExecute($this->holder, $form, $explicitTransitionName);
            $this->flashDump->dump($this->handler->getLogger(), $this->getPresenter());
            $this->finalRedirect();
        } catch (ApplicationHandlerException $exception) {
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

    /**
     * @return bool
     */
    private function canEdit() {
        return $this->getMachine()->getPrimaryMachine()->getState() != BaseMachine::STATE_INIT && $this->holder->getPrimaryHolder()->isModifiable();
    }

    /**
     * @throws AbortException
     */
    private function finalRedirect() {
        if ($this->redirectCallback) {
            $id = $this->holder->getPrimaryHolder()->getModel()->getPrimary(false);
            ($this->redirectCallback)($id, $this->holder->getPrimaryHolder()->getEvent()->getPrimary());
        } else {
            $this->redirect('this');
        }
    }

}

