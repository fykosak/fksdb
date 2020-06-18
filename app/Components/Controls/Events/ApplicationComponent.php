<?php

namespace FKSDB\Components\Events;

use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Events\Machine\BaseMachine;
use FKSDB\Events\Machine\Machine;
use FKSDB\Events\Model\ApplicationHandler;
use FKSDB\Events\Model\ApplicationHandlerException;
use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Logging\FlashMessageDump;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use \Nette\Forms\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\InvalidStateException;
use Nette\Utils\JsonException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 * @method \FKSDB\CoreModule\AuthenticatedPresenter|\FKSDB\CoreModule\BasePresenter getPresenter($need = true)
 */
class ApplicationComponent extends BaseComponent {

    /**
     * @var ApplicationHandler
     */
    private $handler;

    /**
     * @var Holder
     */
    private $holder;

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
     * @param Container $container
     * @param ApplicationHandler $handler
     * @param Holder $holder
     */
    public function __construct(Container $container, ApplicationHandler $handler, Holder $holder) {
        parent::__construct($container);
        $this->handler = $handler;
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

    /**
     * @return callable
     */
    public function getRedirectCallback() {
        return $this->redirectCallback;
    }

    /**
     * @param callable $redirectCallback
     * @return void
     */
    public function setRedirectCallback(callable $redirectCallback) {
        $this->redirectCallback = $redirectCallback;
    }

    /**
     * Syntactic sugar for the template.
     */
    public function isEventAdmin(): bool {
        $event = $this->holder->getPrimaryHolder()->getEvent();
        return $this->getPresenter()->getContestAuthorizator()->isAllowed($event, 'application', $event->getContest());
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function render() {
        $this->renderForm();
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function renderForm() {
        if (!$this->templateFile) {
            throw new InvalidStateException('Must set template for the application form.');
        }

        $this->template->setFile($this->templateFile);
        $this->template->holder = $this->holder;
        $this->template->event = $this->holder->getPrimaryHolder()->getEvent();
        $this->template->primaryModel = $this->holder->getPrimaryHolder()->getModel();
        $this->template->primaryMachine = $this->getMachine()->getPrimaryMachine();
        $this->template->render();
    }

    /**
     * @param string $mode
     * @return void
     * @throws BadRequestException
     */
    public function renderInline($mode) {
        $this->template->mode = $mode;
        $this->template->holder = $this->holder;
        $this->template->primaryModel = $this->holder->getPrimaryHolder()->getModel();
        $this->template->primaryMachine = $this->getMachine()->getPrimaryMachine();
        $this->template->canEdit = $this->canEdit();
        $this->template->state = $this->holder->getPrimaryHolder()->getModelState();
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
        foreach ($this->holder->getBaseHolders() as $name => $baseHolder) {
            $baseMachine = $this->getMachine()->getBaseMachine($name);
            if (!$baseHolder->isVisible()) {
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
            $saveSubmit = $form->addSubmit('save', _('Save'));
            $saveSubmit->setOption('row', 1);
            $saveSubmit->onClick[] = function (SubmitButton $button) {
                $buttonForm = $button->getForm();
                $this->handleSubmit($buttonForm);
            };
        }
        /*
         * Create transition buttons
         */
        $primaryMachine = $this->getMachine()->getPrimaryMachine();
        $transitionSubmit = null;

        foreach ($primaryMachine->getAvailableTransitions($this->holder, $this->holder->getPrimaryHolder()->getModelState(), BaseMachine::EXECUTABLE | BaseMachine::VISIBLE) as $transition) {
            $transitionName = $transition->getName();
            $submit = $form->addSubmit($transitionName, $transition->getLabel());

            $submit->onClick[] = function (SubmitButton $button) use ($transitionName) {
                $form = $button->getForm();
                $this->handleSubmit($form, $transitionName);
            };

            if ($transition->isCreating()) {
                if ($transitionSubmit !== false) {
                    $transitionSubmit = $submit;
                } elseif ($transitionSubmit) {
                    $transitionSubmit = false; // if there is more than one submit set no one
                }
            }
            $submit->getControlPrototype()->addAttributes(['btn btn-' . $transition->getType()]);
        }

        /*
         * Create cancel button
         */
        $submit = $form->addSubmit('cancel', _('Storno'));
        $submit->setOption('row', 1);
        $submit->setValidationScope(false);
        $submit->getControlPrototype()->addAttributes(['class' => 'btn-warning']);
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
        } elseif ($transitionSubmit) {
            $transitionSubmit->getControlPrototype()->data['submit-on'] = 'this';
        }

        return $result;
    }

    /**
     * @param Form $form
     * @param null $explicitTransitionName
     * @throws AbortException
     * @throws JsonException
     * @throws BadRequestException
     */
    public function handleSubmit(Form $form, $explicitTransitionName = null) {
        $this->execute($form, $explicitTransitionName);
    }

    /**
     * @param $transitionName
     * @throws AbortException
     * @throws JsonException
     * @throws BadRequestException
     */
    public function handleTransition($transitionName) {
        $this->execute(null, $transitionName);
    }

    /**
     * @param Form|null $form
     * @param null $explicitTransitionName
     * @throws AbortException
     * @throws JsonException
     * @throws BadRequestException
     */
    private function execute(Form $form = null, $explicitTransitionName = null) {
        try {
            $this->handler->storeAndExecute($this->holder, $form, $explicitTransitionName);
            FlashMessageDump::dump($this->handler->getLogger(), $this->getPresenter());
            $this->finalRedirect();
        } catch (ApplicationHandlerException $exception) {
            /* handled elsewhere, here it's to just prevent redirect */
            FlashMessageDump::dump($this->handler->getLogger(), $this->getPresenter());
            if (!$form) { // w/out form we don't want to show anything with the same GET params
                $this->finalRedirect();
            }
        }
    }

    /**
     * @return Machine
     * @throws BadRequestException
     */
    private function getMachine() {
        return $this->handler->getMachine($this->holder);
    }

    /**
     * @return bool
     */
    private function canEdit() {
        return $this->holder->getPrimaryHolder()->getModelState() != BaseMachine::STATE_INIT && $this->holder->getPrimaryHolder()->isModifiable();
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
