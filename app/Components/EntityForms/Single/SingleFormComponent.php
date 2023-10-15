<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Single;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Components\EntityForms\Fyziklani\FormProcessing;
use FKSDB\Components\Forms\Containers\Models\ContainerWithOptions;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Persons\Resolvers\SelfACLResolver;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Models\Utils\FormUtils;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\AbortException;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Neon\Exception;

/**
 * @method BasePresenter getPresenter($need = true)
 * @phpstan-extends EntityFormComponent<EventParticipantModel>
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
abstract class SingleFormComponent extends EntityFormComponent
{
    protected ReferencedPersonFactory $referencedPersonFactory;
    protected SingleReflectionFormFactory $reflectionFormFactory;
    protected EventParticipantService $eventParticipantService;

    protected EventParticipantMachine $machine;
    protected ?PersonModel $loggedPerson;
    protected EventModel $event;

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
            $this->getPersonFieldsDefinition(),
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

        foreach ($this->getParticipantFieldsDefinition() as $field => $metadata) {
            $this->reflectionFormFactory->addToContainer(
                $form,
                'event_participant',
                $field,
                $metadata,
                new FieldLevelPermission(FieldLevelPermission::ALLOW_FULL, FieldLevelPermission::ALLOW_FULL)
            );
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
