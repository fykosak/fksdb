<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Events;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Components\Schedule\Input\ExistingPaymentException;
use FKSDB\Components\Schedule\Input\FullCapacityException;
use FKSDB\Models\Events\Exceptions\MachineExecutionException;
use FKSDB\Models\Events\FormAdjustments\FormAdjustment;
use FKSDB\Models\Events\Model\ApplicationHandlerException;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Processing\Processing;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\ORM\Services\Exceptions\DuplicateApplicationException;
use FKSDB\Models\Persons\ModelDataConflictException;
use FKSDB\Models\Persons\Resolvers\SelfACLResolver;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Utils\FormUtils;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Modules\PublicModule\ApplicationPresenter;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\Database\Connection;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Nette\Utils\ArrayHash;
use Tracy\Debugger;

/**
 * @method BasePresenter getPresenter($need = true)
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
abstract class ApplicationComponent extends BaseComponent
{
    private BaseHolder $holder;
    private Connection $connection;
    private EventModel $event;

    protected ReferencedPersonFactory $referencedPersonFactory;
    protected EventParticipantService $eventParticipantService;

    /**
     * @phpstan-var EventParticipantMachine<BaseHolder> $machine
     */
    private EventParticipantMachine $machine;

    /**
     * @phpstan-param EventParticipantMachine<BaseHolder> $machine
     */
    public function __construct(
        Container $container,
        BaseHolder $holder,
        EventParticipantMachine $machine,
        EventModel $event
    ) {
        parent::__construct($container);
        $this->holder = $holder;
        $this->machine = $machine;
        $this->event = $event;
    }

    public function inject(
        Connection $connection,
        ReferencedPersonFactory $referencedPersonFactory,
        EventParticipantService $eventParticipantService
    ): void {
        $this->connection = $connection;
        $this->referencedPersonFactory = $referencedPersonFactory;
        $this->eventParticipantService = $eventParticipantService;
    }

    final public function render(): void
    {
        $this->template->render(
            __DIR__ . DIRECTORY_SEPARATOR . 'layout.application.form.latte',
            ['holder' => $this->holder]
        );
    }

    /**
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     * @throws OmittedControlException
     */
    protected function createComponentForm(): FormControl
    {
        $result = new FormControl($this->getContext());
        $form = $result->getForm();

        $this->createFormContainer($form);
        /*
         * Create save (no transition) button
         */
        $saveSubmit = $form->addSubmit('save', _('button.save'));
        $saveSubmit->onClick[] = fn(SubmitButton $button) => $this->handleSubmit($button->getForm());

        /*
         * Create transition buttons
         */
        foreach (
            $this->machine->getAvailableTransitions($this->holder, $this->holder->getModelState()) as $transition
        ) {
            $submit = $form->addSubmit($transition->getId(), $transition->label()->toHtml());

            if (!$transition->getValidation()) {
                $submit->setValidationScope([]);
            }

            $submit->onClick[] = fn(SubmitButton $button) => $this->handleSubmit($button->getForm(), $transition);

            $submit->getControlPrototype()->addAttributes(
                ['class' => 'btn btn-outline-' . $transition->behaviorType->value]
            );
        }

        /*
         * Custom adjustments
         */
        foreach ($this->getFormAdjustment() as $adjustment) {
            $adjustment->adjust($form, $this->holder);
        }

        return $result;
    }

    /**
     * @phpstan-return FormAdjustment<BaseHolder>[]
     */
    protected function getFormAdjustment(): array
    {
        return [];
    }

    /**
     * @throws ForbiddenRequestException
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    public function createFormContainer(Form $form): void
    {
        $container = new ModelContainer($this->getContext(), 'event_participant');
        $personContainer = $this->referencedPersonFactory->createReferencedPerson(
            $this->getPersonFieldsDefinition(),
            $this->event->getContestYear(),
            PersonSearchContainer::SEARCH_ID,
            false,
            new SelfACLResolver(
                $this->holder->getModel() ?? EventParticipantModel::RESOURCE_ID,
                'organizer',
                $this->event->event_type->contest,
                $this->container
            ),
            $this->event
        );
        $personContainer->searchContainer->setOption('label', _('Participant'));
        $personContainer->referencedContainer->setOption('label', _('Participant'));
        $container->addComponent($personContainer, 'person_id');

        foreach ($this->getParticipantFieldsDefinition() as $field => $metadata) {
            $container->addField(
                $field,
                $metadata,
                new FieldLevelPermission(FieldLevelPermission::ALLOW_FULL, FieldLevelPermission::ALLOW_FULL)
            );
        }

        $form->addComponent($container, 'event_participant');
    }

    /**
     * @phpstan-return EvaluatedFieldsDefinition
     */
    abstract protected function getPersonFieldsDefinition(): array;

    /**
     * @phpstan-return array<string, array<string, mixed>>
     */
    abstract protected function getParticipantFieldsDefinition(): array;

    /**
     * @return Processing[]
     */
    protected function getProcessing(): array
    {
        return [];
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
                    foreach ($this->getProcessing() as $processing) {
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
                    $this->eventParticipantService->storeModel(
                        (array)$values['participant'],
                        $this->holder->getModel()
                    );

                    if ($transition) {
                        $this->machine->execute($transition, $this->holder);
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
                $this->getPresenter()->flashMessage($transition->getSuccessLabel(), Message::LVL_SUCCESS);
            }
            $this->finalRedirect();
        } catch (ApplicationHandlerException $exception) {
            /* handled elsewhere, here it's to just prevent redirect */
        }
    }

    private function finalRedirect(): void
    {
        $this->getPresenter()->redirect(
            'this',
            [
                'eventId' => $this->event->event_id,
                'id' => $this->holder->getModel()->getPrimary(),
                ApplicationPresenter::PARAM_AFTER => true,
            ]
        );
    }
}
