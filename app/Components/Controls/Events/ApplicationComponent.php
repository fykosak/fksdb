<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Events;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Schedule\Input\ExistingPaymentException;
use FKSDB\Components\Schedule\Input\FullCapacityException;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\Events\Exceptions\MachineExecutionException;
use FKSDB\Models\Events\Exceptions\SubmitProcessingException;
use FKSDB\Models\Events\Model\ApplicationHandlerException;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\Exceptions\DuplicateApplicationException;
use FKSDB\Models\Persons\ModelDataConflictException;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Utils\FormUtils;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Modules\PublicModule\ApplicationPresenter;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\Database\Connection;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

/**
 * @method BasePresenter getPresenter($need = true)
 */
class ApplicationComponent extends BaseComponent
{
    private BaseHolder $holder;
    private Connection $connection;
    private EventDispatchFactory $eventDispatchFactory;
    /**
     * @phpstan-var EventParticipantMachine<BaseHolder> $machine
     */
    private EventParticipantMachine $machine;

    /**
     * @phpstan-param EventParticipantMachine<BaseHolder> $machine
     */
    public function __construct(Container $container, BaseHolder $holder, EventParticipantMachine $machine)
    {
        parent::__construct($container);
        $this->holder = $holder;
        $this->machine = $machine;
    }

    public function inject(Connection $connection, EventDispatchFactory $eventDispatchFactory): void
    {
        $this->eventDispatchFactory = $eventDispatchFactory;
        $this->connection = $connection;
    }

    private function getTemplateFile(): string
    {
        $template = $this->eventDispatchFactory->getFormLayout($this->holder->event);
        if (stripos($template, '.latte') !== false) {
            return $template;
        } else {
            return __DIR__ . DIRECTORY_SEPARATOR . "layout.application.$template.latte";
        }
    }

    final public function render(): void
    {
        $this->renderForm();
    }

    final public function renderForm(): void
    {
        $this->template->render($this->getTemplateFile(), ['holder' => $this->holder]);
    }

    protected function createComponentForm(): FormControl
    {
        $result = new FormControl($this->getContext());
        $form = $result->getForm();

        $container = $this->holder->createFormContainer($this->getContext());
        $form->addComponent($container, 'participant');
        /*
         * Create save (no transition) button
         */
        $saveSubmit = null;
        if ($this->canEdit()) {
            $saveSubmit = $form->addSubmit('save', _('button.save'));
            $saveSubmit->onClick[] = fn(SubmitButton $button) => $this->handleSubmit($button->getForm());
        }

        /*
         * Create transition buttons
         */
        $transitionSubmit = null;

        foreach (
            $this->machine->getAvailableTransitions($this->holder, $this->holder->getModelState()) as $transition
        ) {
            $submit = $form->addSubmit($transition->getId(), $transition->label()->toHtml());

            if (!$transition->getValidation()) {
                $submit->setValidationScope([]);
            }

            $submit->onClick[] = fn(SubmitButton $button) => $this->handleSubmit($button->getForm(), $transition);

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
        $cancelSubmit = $form->addSubmit('cancel', _('button.cancel'));
        $cancelSubmit->getControlPrototype()->addAttributes(['class' => 'btn btn-outline-warning']);
        $cancelSubmit->setValidationScope([]);
        $cancelSubmit->onClick[] = fn() => $this->finalRedirect();

        /*
         * Custom adjustments
         */
        foreach ($this->holder->formAdjustments as $adjustment) {
            $adjustment->adjust($form, $this->holder);
        }
        /** @phpstan-ignore-next-line */
        $form->getElementPrototype()->data['submit-on'] = 'enter';
        if ($saveSubmit) {
            /** @phpstan-ignore-next-line */
            $saveSubmit->getControlPrototype()->data['submit-on'] = 'this';
        } elseif ($transitionSubmit) {
            /** @phpstan-ignore-next-line */
            $transitionSubmit->getControlPrototype()->data['submit-on'] = 'this';
        }

        return $result;
    }

    /**
     * @throws \Throwable
     * @phpstan-param Transition<BaseHolder>|null $transition
     */
    public function handleSubmit(Form $form, ?Transition $transition = null): void
    {
        try {
            if (!$transition || $transition->getValidation()) {
                try {
                    $this->connection->beginTransaction();
                    /** @phpstan-var ArrayHash<mixed> $values */
                    $values = $form->getValues();
                    $values = FormUtils::emptyStrToNull($values);
                    Debugger::log(json_encode((array)$values), 'app-form');
                    foreach ($this->holder->processings as $processing) {
                        $processing->process($values);
                    }

                    if ($transition) {
                        $state = $this->holder->getModelState();
                        $transition = Machine::selectTransition(
                            Machine::filterByTarget(
                                Machine::filterBySource($this->machine->transitions, $state),
                                $transition->target
                            )
                        );
                    }
                    if (isset($values['participant'])) {
                        $this->holder->data += (array)$values['participant'];
                    }

                    if ($transition) {
                        $this->machine->execute2($transition, $this->holder);
                    }
                    $this->holder->saveModel();
                    if ($transition) {
                        $transition->callAfterExecute($this->holder);
                    }

                    if ($transition && $transition->isCreating()) {
                        $this->getPresenter()->flashMessage(
                            sprintf(_('Application "%s" created.'), $this->holder->getModel()->person->getFullName()),
                            Message::LVL_SUCCESS
                        );
                    } elseif ($transition) {
                        $this->getPresenter()->flashMessage(
                            sprintf(
                                _('Application state "%s" changed.'),
                                $this->holder->getModel()->person->getFullName()
                            ),
                            Message::LVL_INFO
                        );
                    }
                    $this->getPresenter()->flashMessage(
                        sprintf(_('Application "%s" saved.'), $this->holder->getModel()->person->getFullName()),
                        Message::LVL_SUCCESS
                    );
                    $this->connection->commit();
                } catch (
                    ModelDataConflictException |
                    DuplicateApplicationException |
                    MachineExecutionException |
                    SubmitProcessingException |
                    FullCapacityException |
                    ExistingPaymentException $exception
                ) {
                    $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
                    /** @phpstan-var ReferencedId<PersonModel> $referencedId */
                    foreach ($form->getComponents(true, ReferencedId::class) as $referencedId) {
                        $referencedId->rollback();
                    }
                    $this->connection->rollBack();
                    throw new ApplicationHandlerException(_('Error while saving the application.'), 0, $exception);
                }
            } else {
                $this->machine->execute($transition, $this->holder);
                $this->getPresenter()->flashMessage(_('Transition successful'), Message::LVL_SUCCESS);
            }
            $this->finalRedirect();
        } catch (ApplicationHandlerException $exception) {
            /* handled elsewhere, here it's to just prevent redirect */
        }
    }

    private function canEdit(): bool
    {
        return $this->holder->getModelState() != Machine::STATE_INIT && $this->holder->isModifiable();
    }

    private function finalRedirect(): void
    {
        $this->getPresenter()->redirect(
            'this',
            [
                'eventId' => $this->holder->event->event_id,
                'id' => $this->holder->getModel()->getPrimary(),
                ApplicationPresenter::PARAM_AFTER => true,
            ]
        );
    }
}
