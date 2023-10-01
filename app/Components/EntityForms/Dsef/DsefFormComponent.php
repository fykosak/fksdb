<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Dsef;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Components\EntityForms\Fyziklani\FormProcessing;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Persons\Resolvers\SelfACLResolver;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Neon\Neon;

/**
 * @method BasePresenter getPresenter($need = true)
 */
class DsefFormComponent extends EntityFormComponent
{

    protected ReferencedPersonFactory $referencedPersonFactory;
    protected SingleReflectionFormFactory $reflectionFormFactory;
    protected BaseHolder $holder;
    protected EventParticipantMachine $machine;
    protected EventParticipantService $eventParticipantService;
    protected ?PersonModel $loggedPerson;

    public function __construct(
        Container $container,
        ?Model $model,
        BaseHolder $holder,
        EventParticipantMachine $machine,
        ?PersonModel $loggedPerson
    ) {
        parent::__construct($container, $model);
        $this->holder = $holder;
        $this->machine = $machine;
        $this->loggedPerson = $loggedPerson;
    }

    public function injectPrimary(
        ReferencedPersonFactory $referencedPersonFactory,
        SingleReflectionFormFactory $reflectionFormFactory,
        EventParticipantService $eventParticipantService,
    ) {
        $this->referencedPersonFactory = $referencedPersonFactory;
        $this->reflectionFormFactory = $reflectionFormFactory;
        $this->eventParticipantService = $eventParticipantService;
    }

    protected function configureForm(Form $form): void
    {
        $personContainer = $this->referencedPersonFactory->createReferencedPerson(
            $this->getParticipantFieldsDefinition(),
            $this->holder->event->getContestYear(),
            'email',
            true,
            new SelfACLResolver(
                $this->model ?? 'event.participant',
                'organizer',
                $this->holder->event->event_type->contest,
                $this->container
            ),
            $this->holder->event
        );
        $personContainer->searchContainer->setOption('label', _('Participant'));
        $personContainer->referencedContainer->setOption('label', _('Participant'));
        $form->addComponent($personContainer, 'person');

        $participantContainer= $this->reflectionFormFactory->createContainerWithMetadata(
            'event_participant',
            ['lunch_count' => ['required' => false]],
            new FieldLevelPermission(FieldLevelPermission::ALLOW_FULL, FieldLevelPermission::ALLOW_FULL)
        );
        $form->addComponent($participantContainer, 'participant');

        $dsefMorning = $personContainer->referencedContainer['person_schedule'][ScheduleGroupType::DSEF_MORNING];
        $dsefAfternoon = $personContainer->referencedContainer['person_schedule'][ScheduleGroupType::DSEF_AFTERNOON];
        $dsefAllDay = $personContainer->referencedContainer['person_schedule'][ScheduleGroupType::DSEF_ALL_DAY];

        // TODO add conditions to controls
        $dsefMorning->addConditionOn($dsefAllDay, Form::FILLED)
            ->addRule(Form::BLANK, '');
        $dsefAfternoon->addConditionOn($dsefAllDay, Form::FILLED)
            ->addRule(Form::BLANK, '');
    }

    protected function getProcessing(): array
    {
        return [];
    }

    private function getParticipantFieldsDefinition()
    {
        return Neon::decodeFile(__DIR__ . DIRECTORY_SEPARATOR . 'dsef.participant.neon');
    }

    protected function setDefaults(Form $form): void
    {
        if (isset($this->model)) {
            $form->setDefaults($this->model);
            $referencedId = $form->getComponent('person');
            $referencedId->setDefaultValue($this->model->person);
        } else if (isset($this->loggedPerson)) {
            $form->setDefaults(['person' => $this->loggedPerson->person_id]);
        }
    }

    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues('array');
        $values = array_reduce(
            $this->getProcessing(),
            fn(array $prevValue, FormProcessing $item): array => $item($prevValue, $form, $this->event),
            $values
        );

        if (isset($values['person'])) {
            $this->holder->data += (array)$values['person'];
        }

        if (!isset($this->model)) {
            $transition = $this->machine->getImplicitTransition($this->holder);
            $this->machine->execute2($transition, $this->holder);
        }

        $this->eventParticipantService->explorer->beginTransaction();
        $this->eventParticipantService->storeModel(array_merge($values, [
            'event_id' => $this->holder->event->event_id,
            'person_id' => $this->model->person->person_id
        ]));

        $this->holder->saveModel();
        $this->eventParticipantService->explorer->commit();
    }

}
