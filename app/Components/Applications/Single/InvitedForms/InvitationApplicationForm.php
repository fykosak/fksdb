<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Single\InvitedForms;

use FKSDB\Components\Controls\FormControl\FormControl;
use FKSDB\Components\Controls\Transition\TransitionSubmitButton;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Components\Schedule\Input\ExistingPaymentException;
use FKSDB\Components\Schedule\Input\FullCapacityException;
use FKSDB\Models\Authorization\Resource\EventResourceHolder;
use FKSDB\Models\Events\Exceptions\MachineExecutionException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\ReflectionFactory;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\ORM\Services\Exceptions\DuplicateApplicationException;
use FKSDB\Models\Persons\ModelDataConflictException;
use FKSDB\Models\Persons\Resolvers\SelfEventACLResolver;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Transition\Transition;
use FKSDB\Models\Transitions\TransitionsMachineFactory;
use FKSDB\Models\Utils\FormUtils;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Fykosak\Utils\Logging\Message;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Form;
use Tracy\Debugger;

/**
 * @method BasePresenter getPresenter($need = true)
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 * @phpstan-import-type TMeta from ReferencedPersonContainer
 * @phpstan-type RawFieldMetaData array{
 *     required?:bool|(callable(ParticipantHolder):bool),
 *     caption?:string|null|(callable(ParticipantHolder):string|null),
 *     description?:string|null|(callable(ParticipantHolder):string|null)}
 * @phpstan-type RawFieldDefinition array<string,array<string,RawFieldMetaData>> & array{
 *    person_schedule?:array<string,TMeta>
 *  }
 */
abstract class InvitationApplicationForm extends BaseComponent
{
    protected ?EventParticipantModel $model;
    protected EventModel $event;
    protected PersonModel $loggedPerson;

    protected TransitionsMachineFactory $eventDispatchFactory;
    protected ReferencedPersonFactory $referencedPersonFactory;
    protected EventParticipantService $eventParticipantService;
    protected ReflectionFactory $reflectionFactory;

    public function __construct(
        Container $container,
        ?EventParticipantModel $model,
        EventModel $event,
        PersonModel $loggedPerson
    ) {
        parent::__construct($container);
        $this->event = $event;
        $this->model = $model;
        $this->loggedPerson = $loggedPerson;
    }

    public function inject(
        ReferencedPersonFactory $referencedPersonFactory,
        EventParticipantService $eventParticipantService,
        TransitionsMachineFactory $eventDispatchFactory,
        ReflectionFactory $reflectionFactory
    ): void {
        $this->referencedPersonFactory = $referencedPersonFactory;
        $this->reflectionFactory = $reflectionFactory;
        $this->eventParticipantService = $eventParticipantService;
        $this->eventDispatchFactory = $eventDispatchFactory;
    }

    protected function getTemplateFile(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'layout.latte';
    }

    /**
     * @throws NotImplementedException
     */
    final public function render(): void
    {
        $machine = $this->eventDispatchFactory->getParticipantMachine($this->event);
        $this->setDefault();
        $this->template->render(
            $this->getTemplateFile(),
            [
                'model' => $this->model,
                'holder' => $this->model ? $machine->createHolder($this->model) : null,
            ]
        );
    }

    /**
     * @throws BadTypeException
     * @throws ForbiddenRequestException
     * @throws OmittedControlException
     * @throws NotImplementedException
     */
    protected function createComponentForm(): FormControl
    {
        $machine = $this->eventDispatchFactory->getParticipantMachine($this->event);
        $result = new FormControl($this->getContext());
        $form = $result->getForm();

        $this->createFormContainer($form);
        $control = $this->reflectionFactory->createField('person_info', 'agreed');
        $control->addRule(Form::FILLED, _('You have to agree with the privacy policy before submitting.'));
        /*
         * Create save (no transition) button
         */
        if ($this->model) {
            $saveSubmit = $form->addSubmit('save', _('button.save'));
            $saveSubmit->onClick[] = fn(SubmitButton $button) => $this->handleSubmit($button->getForm());
        }

        if ($this->model) {
            $holder = $machine->createHolder($this->model);
            $transitions = $machine->getTransitions()->filterAvailable($holder);
        } else {
            $holder = null;
            $transitions = $machine->getTransitions()
                ->filterBySource(EventParticipantStatus::from(EventParticipantStatus::INIT));
        }
        foreach ($transitions->toArray() as $transition) {
            $submit = new TransitionSubmitButton($transition, $holder);
            $form->addComponent($submit, $transition->getId());
            $submit->onClick[] = fn(SubmitButton $button) => $this->handleSubmit($button->getForm(), $transition);
        }
        return $result;
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
            new SelfEventACLResolver(
                $this->model
                    ? EventResourceHolder::fromOwnResource($this->model)
                    : EventResourceHolder::fromResourceId(EventParticipantModel::RESOURCE_ID, $this->event),
                'organizer',
                $this->event,
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
     * @throws \Throwable
     * @phpstan-param Transition<ParticipantHolder>|null $transition
     */
    public function handleSubmit(Form $form, ?Transition $transition = null): void
    {
        $machine = $this->eventDispatchFactory->getParticipantMachine($this->event);
        try {
            if ($transition && !$transition->validation) {
                $holder = $machine->createHolder($this->model);
                $transition->execute($holder);
                $this->getPresenter()->flashMessage($transition->getSuccessLabel(), Message::LVL_SUCCESS);
            } else {
                /** @phpstan-var array{event_participant:array{person_id:int}} $values */
                $values = $form->getValues('array');
                $values = FormUtils::emptyStrToNull2($values);
                $values['event_participant']['person_container']['person_info']['agreed'] = 1;
                Debugger::log(json_encode((array)$values), 'application');
                /* $values = array_reduce(
                     $this->getProcessing(),
                     function (array $data, Processing $processing) {
                         return $processing->process($data);
                     },
                     $values
                 );*/
                /** @var EventParticipantModel $model */
                $model = $this->eventParticipantService->explorer->getConnection()
                    ->transaction(function () use ($machine, $values, $transition): EventParticipantModel {
                        $model = $this->eventParticipantService->storeModel(
                            array_merge(
                                $values['event_participant'],
                                ['event_id' => $this->event->event_id]
                            ),
                            $this->model
                        );
                        $holder = $machine->createHolder($model);
                        if ($transition) {
                            $transition->execute($holder);
                        }
                        return $model;
                    });
                $this->getPresenter()->flashMessage(
                    isset($this->model)
                        ? sprintf(_('Application "%s" updated.'), $model->person->getFullName())
                        : sprintf(_('Application "%s" created.'), $model->person->getFullName()),
                    Message::LVL_SUCCESS
                );
                $this->getPresenter()->redirect(
                    ':Event:Application:detail',
                    [
                        'eventId' => $this->event->event_id,
                        'id' => $model->getPrimary(),
                    ]
                );
            }
        } catch (AbortException $exception) {
            throw $exception;
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
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        } catch (\Throwable $exception) {
            $this->getPresenter()->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        }
    }

    private function setDefault(): void
    {
        /** @var FormControl $control */
        $control = $this->getComponent('form');
        $form = $control->getForm();
        if (isset($this->model)) {
            $form->setDefaults(['event_participant' => $this->model->toArray()]);
        } elseif (isset($this->loggedPerson)) {
            $form->setDefaults(['event_participant' => ['person_id' => $this->loggedPerson->person_id]]);
        }
    }
    /*
     * @throws \ReflectionException
     * @phpstan-param RawFieldDefinition $definition
     * @phpstan-return EvaluatedFieldsDefinition
    private function evaluateFieldsDefinition(ParticipantHolder $holder, array $definition): array
    {
        foreach ($definition as &$sub) {
            foreach ($sub as &$metadata) {
                if (!is_array($metadata)) {
                    $metadata = ['required' => $metadata];
                }
                foreach ($metadata as &$value) {
                    $value = is_callable($value) ? ($value)($holder) : $value;
                }
            }
        }
        return $definition;
    }
      */

    /**
     * @phpstan-return EvaluatedFieldsDefinition
     */
    abstract protected function getPersonFieldsDefinition(): array;

    /**
     * @phpstan-return array<string, array<string, mixed>>
     */
    abstract protected function getParticipantFieldsDefinition(): array;
}
