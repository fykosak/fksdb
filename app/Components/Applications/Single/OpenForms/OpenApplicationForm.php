<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Single\OpenForms;

use FKSDB\Components\EntityForms\ModelForm;
use FKSDB\Components\EntityForms\Processing\DefaultTransition;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Containers\SearchContainer\PersonSearchContainer;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Services\EventParticipantService;
use FKSDB\Models\Persons\Resolvers\SelfACLResolver;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Models\Transitions\TransitionsMachineFactory;
use FKSDB\Models\Utils\FormUtils;
use FKSDB\Modules\Core\BasePresenter;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;

/**
 * @method BasePresenter getPresenter($need = true)
 * @phpstan-extends ModelForm<EventParticipantModel,array<array{event_participant:array<string,mixed>}>>
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
abstract class OpenApplicationForm extends ModelForm
{
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
     */
    protected function configureForm(Form $form): void
    {
        $container = new ModelContainer($this->container, 'event_participant');
        $personContainer = $this->referencedPersonFactory->createReferencedPerson(
            $this->getPersonFieldsDefinition(),
            $this->event->getContestYear(),
            PersonSearchContainer::SEARCH_EMAIL,
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

    protected function getPostprocessing(): array
    {
        $processing = parent::getPostprocessing();
        if (!isset($this->model)) {
            $processing[] = new DefaultTransition($this->container, $this->machine);//@phpstan-ignore-line
        }
        return $processing;
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
    }

    /**
     * @throws \Throwable
     */
    protected function innerSuccess(array $values, Form $form): EventParticipantModel
    {
        $this->eventParticipantService->explorer->beginTransaction();
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
