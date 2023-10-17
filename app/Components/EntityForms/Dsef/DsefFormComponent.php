<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Dsef;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Components\EntityForms\Fyziklani\Processing\FormProcessing;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Components\Schedule\Input\ScheduleContainer;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\Schedule\ScheduleGroupType;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Persons\Resolvers\SelfACLResolver;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Models\Utils\FormUtils;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\AbortException;
use Nette\DI\Container;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Form;
use Nette\Neon\Exception;
use Nette\Neon\Neon;

/**
 * @method BasePresenter getPresenter($need = true)
 * @phpstan-extends EntityFormComponent<EventParticipantModel>
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
final class DsefFormComponent extends EntityFormComponent
{
    private ReferencedPersonFactory $referencedPersonFactory;
    private SingleReflectionFormFactory $reflectionFormFactory;
    private EventParticipantService $eventParticipantService;

    private EventParticipantMachine $machine;
    private ?PersonModel $loggedPerson;
    private EventModel $event;

    public function __construct(
        Container $container,
        ?Model $model,
        EventModel $event,
        EventParticipantMachine $machine,
        ?PersonModel $loggedPerson
    ) {
        parent::__construct($container, $model);
        $this->event = $event;
        $this->machine = $machine;
        $this->loggedPerson = $loggedPerson;
    }

    public function injectPrimary(
        ReferencedPersonFactory $referencedPersonFactory,
        SingleReflectionFormFactory $reflectionFormFactory,
        EventParticipantService $eventParticipantService
    ): void {
        $this->referencedPersonFactory = $referencedPersonFactory;
        $this->reflectionFormFactory = $reflectionFormFactory;
        $this->eventParticipantService = $eventParticipantService;
    }

    /**
     * @throws Exception
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void
    {
        $container = new ContainerWithOptions($this->container);
        $personContainer = $this->referencedPersonFactory->createReferencedPerson(
            $this->getParticipantFieldsDefinition(),
            $this->event->getContestYear(),
            'email',
            true,
            new SelfACLResolver(
                $this->model ?? EventParticipantModel::RESOURCE_ID,
                'organizer',
                $this->event->event_type->contest,
                $this->container
            ),
            $this->event
        );
        $personContainer->searchContainer->setOption('label', _('Participant'));
        $personContainer->referencedContainer->setOption('label', _('Participant'));
        $form->addComponent($personContainer, 'person_id');

        $this->reflectionFormFactory->addToContainer(
            $form,
            'event_participant',
            'lunch_count',
            ['required' => false],
            new FieldLevelPermission(FieldLevelPermission::ALLOW_FULL, FieldLevelPermission::ALLOW_FULL)
        );

        /**
         * @var ScheduleContainer $dsefMorning
         * @phpstan-ignore-next-line
         */
        $dsefMorning = $personContainer->referencedContainer['person_schedule'][ScheduleGroupType::DSEF_MORNING];
        /**
         * @var ScheduleContainer $dsefMorning
         * @phpstan-ignore-next-line
         */
        $dsefAfternoon = $personContainer->referencedContainer['person_schedule'][ScheduleGroupType::DSEF_AFTERNOON];
        /**
         * @var ScheduleContainer $dsefMorning
         * @phpstan-ignore-next-line
         */
        $dsefAllDay = $personContainer->referencedContainer['person_schedule'][ScheduleGroupType::DSEF_ALL_DAY];
        $halfDayComponents = [];
        foreach ($dsefMorning->getComponents() as $morningSelect) {
            $halfDayComponents[] = $morningSelect;
        }
        foreach ($dsefAfternoon->getComponents() as $afternoonSelect) {
            $halfDayComponents[] = $afternoonSelect;
        }
        /** @var SelectBox $allDaySelect */
        foreach ($dsefAllDay->getComponents() as $allDaySelect) {
            /** @var SelectBox[] $halfDayComponents */
            foreach ($halfDayComponents as $halfDayComponent) {
                $allDaySelect->addConditionOn($halfDayComponent, Form::Filled)
                    ->addRule(
                        Form::Blank,
                        _('You must register both morning and afternoon groups or only the all day group.')
                    );
                $allDaySelect->addConditionOn($halfDayComponent, Form::Blank)
                    ->addRule(
                        Form::Filled,
                        _('You must register both morning and afternoon groups or only the all day group.')
                    );
                $halfDayComponent->addConditionOn($allDaySelect, Form::Filled)
                    ->addRule(
                        Form::Blank,
                        _('You must register both morning and afternoon groups or only the all day group.')
                    );
                $halfDayComponent->addConditionOn($allDaySelect, Form::Blank)
                    ->addRule(
                        Form::Filled,
                        _('You must register both morning and afternoon groups or only the all day group.')
                    );
            }
        }
    }

    /**
     * @return FormProcessing[]
     */
    protected function getProcessing(): array
    {
        return [];
    }

    /**
     * @throws Exception
     * @phpstan-return EvaluatedFieldsDefinition
     */
    private function getParticipantFieldsDefinition(): array
    {
        return Neon::decodeFile(__DIR__ . DIRECTORY_SEPARATOR . 'dsef.participant.neon');
    }

    protected function setDefaults(Form $form): void
    {
        if (isset($this->model)) {
            $form->setDefaults($this->model);
        } elseif (isset($this->loggedPerson)) {
            $form->setDefaults(['person_id' => $this->loggedPerson->person_id]);
        }
    }

    /**
     * @throws \Throwable
     */
    protected function handleFormSuccess(Form $form): void
    {
        /** @phpstan-var array<mixed> $values */
        $values = $form->getValues('array');
        $this->eventParticipantService->explorer->beginTransaction();
        try {
            /*  $values = array_reduce(
                  $this->getProcessing(),
                  fn(array $prevValue, FormProcessing $item): array => $item($prevValue, $form, $this->event),
                  $values
              );*/

            $eventParticipant = $this->eventParticipantService->storeModel(
                array_merge(FormUtils::emptyStrToNull2($values), [
                    'event_id' => $this->event->event_id,
                ]),
                $this->model
            );

            if (!isset($this->model)) {
                $holder = $this->machine->createHolder($eventParticipant);
                $transition = $this->machine->getImplicitTransition($holder);
                $this->machine->execute($transition, $holder);
            }
            $this->eventParticipantService->explorer->commit();
            $this->getPresenter()->flashMessage(
                isset($this->model)
                    ? _('Application has been updated')
                    : _('Application has been created'),
                Message::LVL_SUCCESS
            );
            $this->getPresenter()->redirect('detail', ['id' => $eventParticipant->event_participant_id]);
        } catch (AbortException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            $this->eventParticipantService->explorer->rollBack();
            throw $exception;
        }
    }
}
