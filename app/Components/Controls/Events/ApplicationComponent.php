<?php

namespace FKSDB\Components\Controls\Events;

use FKSDB\Models\Authorization\ContestAuthorizator;
use FKSDB\Components\Controls\BaseComponent;
use FKSDB\Models\Events\Machine\BaseMachine;
use FKSDB\Models\Events\Machine\Machine;
use FKSDB\Models\Events\Model\ApplicationHandler;
use FKSDB\Models\Events\Model\ApplicationHandlerException;
use FKSDB\Models\Events\Model\Holder\Holder;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Logging\FlashMessageDump;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Modules\Core\BasePresenter;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\InvalidStateException;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 * @method AuthenticatedPresenter|BasePresenter getPresenter($need = true)
 */
class ApplicationComponent extends BaseComponent {

    private ApplicationHandler $handler;
    private Holder $holder;
    /** @var callable ($primaryModelId, $eventId) */
    private $redirectCallback;
    private string $templateFile;
    private ContestAuthorizator $contestAuthorizator;

    public function __construct(Container $container, ApplicationHandler $handler, Holder $holder) {
        parent::__construct($container);
        $this->handler = $handler;
        $this->holder = $holder;
    }

    public function injectContestAuthorizator(ContestAuthorizator $contestAuthorizator): void {
        $this->contestAuthorizator = $contestAuthorizator;
    }

    /**
     * @param string $template name of the standard template or whole path
     */
    public function setTemplate(string $template): void {
        if (stripos($template, '.latte') !== false) {
            $this->templateFile = $template;
        } else {
            $this->templateFile = __DIR__ . DIRECTORY_SEPARATOR . "layout.application.$template.latte";
        }
    }

    public function setRedirectCallback(callable $redirectCallback): void {
        $this->redirectCallback = $redirectCallback;
    }

    /**
     * Syntactic sugar for the template.
     */
    public function isEventAdmin(): bool {
        $event = $this->holder->getPrimaryHolder()->getEvent();
        return $this->contestAuthorizator->isAllowed($event, 'application', $event->getContest());
    }

    public function render(): void {
        $this->renderForm();
    }

    public function renderForm(): void {
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
     * @return FormControl
     * @throws BadTypeException
     */
    protected function createComponentForm(): FormControl {
        $result = new FormControl($this->getContext());
        $form = $result->getForm();

        /*
         * Create containers
         */
        foreach ($this->holder->getBaseHolders() as $name => $baseHolder) {
            if (!$baseHolder->isVisible()) {
                continue;
            }
            $container = $baseHolder->createFormContainer();
            $form->addComponent($container, $name);
        }

        /*
         * Create save (no transition) button
         */
        $saveSubmit = null;
        if ($this->canEdit()) {
            $saveSubmit = $form->addSubmit('save', _('Save'));
            $saveSubmit->onClick[] = function (SubmitButton $button): void {
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

            $submit->onClick[] = function (SubmitButton $button) use ($transitionName): void {
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
            $submit->getControlPrototype()->addAttributes(['btn btn-' . $transition->getBehaviorType()]);
        }

        /*
         * Create cancel button
         */
        $submit = $form->addSubmit('cancel', _('Cancel'));
        $submit->setValidationScope(null);
        $submit->getControlPrototype()->addAttributes(['class' => 'btn-warning']);
        $submit->onClick[] = function (): void {
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

    public function handleSubmit(Form $form, ?string $explicitTransitionName = null): void {
        try {
            $this->handler->storeAndExecuteForm($this->holder, $form, $explicitTransitionName);
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

    private function getMachine(): Machine {
        return $this->handler->getMachine();
    }

    private function canEdit(): bool {
        return $this->holder->getPrimaryHolder()->getModelState() != \FKSDB\Models\Transitions\Machine\Machine::STATE_INIT && $this->holder->getPrimaryHolder()->isModifiable();
    }

    private function finalRedirect(): void {
        if ($this->redirectCallback) {
            $id = $this->holder->getPrimaryHolder()->getModel()->getPrimary(false);
            ($this->redirectCallback)($id, $this->holder->getPrimaryHolder()->getEvent()->getPrimary());
        } else {
            $this->redirect('this');
        }
    }
}
