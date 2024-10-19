<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Single\OpenForms;

use FKSDB\Components\EntityForms\ModelForm;
use FKSDB\Components\EntityForms\Processing\DefaultTransition;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Components\Schedule\Input\ScheduleContainer;
use FKSDB\Components\Schedule\Input\SectionContainer;
use FKSDB\Models\Authorization\Resource\EventResourceHolder;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Persons\Resolvers\SelfEventACLResolver;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Models\Transitions\TransitionsMachineFactory;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;
use Tracy\Debugger;

/**
 * @method BasePresenter getPresenter($need = true)
 * @phpstan-import-type TMeta from SectionContainer
 * @phpstan-extends ModelForm<EventParticipantModel,array<array{event_participant:array<string,mixed>}>>
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
abstract class OpenApplicationForm extends ModelForm
{
    protected const ScheduleContainer = 'schedule_container'; // phpcs:ignore
    protected const PersonContainer = 'person_id';// phpcs:ignore

    protected ReferencedPersonFactory $referencedPersonFactory;
    protected EventParticipantService $eventParticipantService;
    protected EventParticipantMachine $machine;
    protected ?PersonModel $loggedPerson;
    protected EventModel $event;

    public function __construct(
        Container $container,
        ?Model $model,
        EventModel $event,
        ?PersonModel $loggedPerson
    ) {
        $this->event = $event;
        parent::__construct($container, $model);
        $this->loggedPerson = $loggedPerson;
    }

    /**
     * @throws NotImplementedException
     */
    public function injectPrimary(
        ReferencedPersonFactory $referencedPersonFactory,
        EventParticipantService $eventParticipantService,
        TransitionsMachineFactory $eventDispatchFactory
    ): void {
        $this->referencedPersonFactory = $referencedPersonFactory;
        $this->eventParticipantService = $eventParticipantService;
        $this->machine = $eventDispatchFactory->getParticipantMachine($this->event);
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws ForbiddenRequestException
     * @throws BadRequestException
     */
    protected function configureForm(Form $form): void
    {
        $container = new ModelContainer($this->container, 'event_participant');
        $personContainer = $this->referencedPersonFactory->createReferencedPerson(
            $this->getPersonFieldsDefinition(),
            $this->event->getContestYear(),
            PersonSearchContainer::SEARCH_EMAIL,
            true,
            new SelfEventACLResolver(
                $this->model
                    ? EventResourceHolder::fromOwnResource($this->model)
                    : EventResourceHolder::fromResourceId(EventParticipantModel::RESOURCE_ID, $this->event),
                'organizer',
                $this->event,
                $this->container
            )
        );
        $personContainer->searchContainer->setOption('label', _('Participant'));
        $personContainer->referencedContainer->setOption('label', _('Participant'));
        $container->addComponent($personContainer, self::PersonContainer);
        $scheduleDefinition = $this->getScheduleDefinition();
        if ($scheduleDefinition) {
            $container->addComponent(
                new ScheduleContainer($this->container, $scheduleDefinition, $this->event),
                self::ScheduleContainer
            );
        }

        foreach ($this->getParticipantFieldsDefinition() as $field => $metadata) {
            $container->addField(
                $field,
                $metadata,
                new FieldLevelPermission(FieldLevelPermission::ALLOW_FULL, FieldLevelPermission::ALLOW_FULL)
            );
        }

        $form->addComponent($container, 'event_participant');
    }

    protected function getPostprocessing(): array
    {
        $processing = parent::getPostprocessing();
        if (!isset($this->model)) {
            $processing[] = new DefaultTransition($this->container, $this->machine); //@phpstan-ignore-line
        }
        return $processing;
    }

    /**
     * @phpstan-return array<string,TMeta>|null
     */
    protected function getScheduleDefinition(): ?array
    {
        return null;
    }
    /**
     * @phpstan-return EvaluatedFieldsDefinition
     */
    abstract protected function getPersonFieldsDefinition(): array;

    /**
     * @phpstan-return array<string, array<string, mixed>>
     */
    abstract protected function getParticipantFieldsDefinition(): array;

    final protected function setDefaults(Form $form): void
    {
        if (isset($this->model)) {
            $form->setDefaults(['event_participant' => $this->model->toArray()]);
        } elseif (isset($this->loggedPerson)) {
            $form->setDefaults(['event_participant' => ['person_id' => $this->loggedPerson->person_id]]);
        }
        /** @var SectionContainer $scheduleContainer */
        foreach ($form->getComponents(true, SectionContainer::class) as $scheduleContainer) {
            $scheduleContainer->setPerson(isset($this->model) ? $this->model->person : $this->loggedPerson);
        }
    }

    /**
     * @throws \Throwable
     */
    protected function innerSuccess(array $values, Form $form): EventParticipantModel
    {
        /**
         * @var ReferencedId<PersonModel> $referencedId
         * @phpstan-ignore-next-line
         */
        $referencedId = $form['event_participant'][self::PersonContainer];
        /**
         * @var ScheduleContainer $scheduleContainer
         * @phpstan-ignore-next-line
         */
        $scheduleContainer = $form['event_participant'][self::ScheduleContainer];
        Debugger::barDump($form);
        $person = $referencedId->getModel();
        if ($person) {
            $scheduleContainer->save($person);
        }
        return $this->eventParticipantService->storeModel(
            array_merge($values['event_participant'], [
                'event_id' => $this->event->event_id,
            ]),
            $this->model
        );
    }

    protected function successRedirect(Model $model): void
    {
        $this->getPresenter()->flashMessage(
            isset($this->model)
                ? _('Application has been updated')
                : _('Application has been created'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('detail', ['id' => $model->event_participant_id]);
    }
}
