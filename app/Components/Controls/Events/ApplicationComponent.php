<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Events;

use FKSDB\Models\Authorization\EventAuthorizator;
use FKSDB\Models\Events\Model\ApplicationHandler;
use FKSDB\Models\Events\Model\ApplicationHandlerException;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\InvalidStateException;
use Fykosak\Utils\Logging\FlashMessageDump;

/**
 * @method AuthenticatedPresenter|BasePresenter getPresenter($need = true)
 */
class ApplicationComponent extends BaseComponent
{

    private ApplicationHandler $handler;
    private BaseHolder $holder;
    /** @var callable ($primaryModelId, $eventId) */
    private $redirectCallback;
    private string $templateFile;
    private EventAuthorizator $eventAuthorizator;

    public function __construct(Container $container, ApplicationHandler $handler, BaseHolder $holder)
    {
        parent::__construct($container);
        $this->handler = $handler;
        $this->holder = $holder;
    }

    public function injectContestAuthorizator(EventAuthorizator $eventAuthorizator): void
    {
        $this->eventAuthorizator = $eventAuthorizator;
    }

    /**
     * @param string $template name of the standard template or whole path
     */
    public function setTemplate(string $template): void
    {
        if (stripos($template, '.latte') !== false) {
            $this->templateFile = $template;
        } else {
            $this->templateFile = __DIR__ . DIRECTORY_SEPARATOR . "layout.application.$template.latte";
        }
    }

    public function setRedirectCallback(callable $redirectCallback): void
    {
        $this->redirectCallback = $redirectCallback;
    }

    /**
     * Syntactic sugar for the template.
     */
    public function isEventAdmin(): bool
    {
        $event = $this->holder->event;
        return $this->eventAuthorizator->isAllowed($event, 'application', $event);
    }

    final public function render(): void
    {
        $this->renderForm();
    }

    final public function renderForm(): void
    {
        if (!$this->templateFile) {
            throw new InvalidStateException('Must set template for the application form.');
        }

        $this->template->holder = $this->holder;
        $this->template->event = $this->holder->event;
        $this->template->primaryMachine = $this->handler->getMachine();
        $this->template->render($this->templateFile);
    }

    /**
     * @throws BadTypeException
     */
    protected function createComponentForm(): FormControl
    {
        $result = new FormControl($this->getContext());
        $form = $result->getForm();

        $container = $this->holder->createFormContainer();
        $form->addComponent($container, $this->holder->name);

        /*
         * Create save (no transition) button
         */
        $saveSubmit = null;
        if ($this->canEdit()) {
            $saveSubmit = $form->addSubmit('save', _('Save'));
            $saveSubmit->onClick[] = fn(SubmitButton $button) => $this->handleSubmit($button->getForm());
        }

        /*
         * Create transition buttons
         */
        $primaryMachine = $this->handler->getMachine();
        $transitionSubmit = null;

        foreach (
            $primaryMachine->getAvailableTransitions(
                $this->holder,
                $this->holder->getModelState()
            ) as $transition
        ) {
            $transitionName = $transition->getId();
            $submit = $form->addSubmit($transitionName, $transition->getLabel());

            if (!$transition->getValidation()) {
                $submit->setValidationScope([]);
            }

            $submit->onClick[] = fn(SubmitButton $button) =>
                $this->handleSubmit($button->getForm(), $transitionName, $transition->getValidation());

            if ($transition->isCreating()) {
                $transitionSubmit = $submit;
            }

            $submit->getControlPrototype()->addAttributes(
                ['class' => 'btn btn-outline-' . $transition->behaviorType->value]
            );
        }

        /*
         * Create cancel button
         */
        $cancelSubmit = $form->addSubmit('cancel', _('Cancel'));
        $cancelSubmit->getControlPrototype()->addAttributes(['class' => 'btn btn-outline-warning']);
        $cancelSubmit->setValidationScope([]);
        $cancelSubmit->onClick[] = fn() => $this->finalRedirect();

        /*
         * Custom adjustments
         */
        $this->holder->adjustForm($form);
        $form->getElementPrototype()->data['submit-on'] = 'enter';
        if ($saveSubmit) {
            $saveSubmit->getControlPrototype()->data['submit-on'] = 'this';
        } elseif ($transitionSubmit) {
            $transitionSubmit->getControlPrototype()->data['submit-on'] = 'this';
        }

        return $result;
    }

    /*
     * Store data and transition
     */
    public function handleSubmit(Form $form, ?string $explicitTransitionName = null, bool $storeData = true): void
    {
        try {
            if ($storeData) {
                $this->handler->storeAndExecuteForm($this->holder, $form, $explicitTransitionName);
            } else {
                $this->handler->onlyExecute($this->holder, $explicitTransitionName);
            }
            FlashMessageDump::dump($this->handler->getLogger(), $this->getPresenter());
            $this->finalRedirect();
        } catch (ApplicationHandlerException $exception) {
            /* handled elsewhere, here it's to just prevent redirect */
            FlashMessageDump::dump($this->handler->getLogger(), $this->getPresenter());
        }
    }

    private function canEdit(): bool
    {
        return $this->holder->getModelState()
            != Machine::STATE_INIT && $this->holder->isModifiable();
    }

    private function finalRedirect(): void
    {
        if ($this->redirectCallback) {
            $model = $this->holder->getModel();
            $id = $model ? $model->getPrimary(false) : null;
            ($this->redirectCallback)($id, $this->holder->event->getPrimary());
        } else {
            $this->redirect('this');
        }
    }
}
