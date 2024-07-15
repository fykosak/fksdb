<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Components\EntityForms\Fyziklani\Processing\FormProcessing;
use FKSDB\Components\EntityForms\Fyziklani\Processing\SchoolsPerTeam\SchoolsPerTeamException;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Controls\CaptchaBox;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Email\FOF\MemberInfoMail;
use FKSDB\Models\Email\FOF\OrganizerInfoMail;
use FKSDB\Models\Email\FOF\TeacherInfoMail;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\ReflectionFactory;
use FKSDB\Models\ORM\Services\Fyziklani\TeamMemberService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\Persons\Resolvers\SelfACLResolver;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Machine\TeamMachine;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\AbortException;
use Nette\Application\ForbiddenRequestException;
use Nette\Database\UniqueConstraintViolationException;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\InvalidStateException;

/**
 * @phpstan-extends EntityFormComponent<TeamModel2>
 * @phpstan-import-type EvaluatedFieldMetaData from ReferencedPersonContainer
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
abstract class TeamForm extends EntityFormComponent
{
    protected ReflectionFactory $reflectionFormFactory;
    protected TeamMachine $machine;
    protected ReferencedPersonFactory $referencedPersonFactory;
    protected EventModel $event;
    protected TeamService2 $teamService;
    protected TeamMemberService $teamMemberService;
    protected bool $isOrganizer;

    public function __construct(
        TeamMachine $machine,
        EventModel $event,
        Container $container,
        ?Model $model,
        bool $isOrganizer
    ) {
        parent::__construct($container, $model);
        $this->machine = $machine;
        $this->event = $event;
        $this->isOrganizer = $isOrganizer;
    }

    final public function injectPrimary(
        TeamService2 $teamService,
        TeamMemberService $teamMemberService,
        ReflectionFactory $reflectionFormFactory,
        ReferencedPersonFactory $referencedPersonFactory
    ): void {
        $this->reflectionFormFactory = $reflectionFormFactory;
        $this->referencedPersonFactory = $referencedPersonFactory;
        $this->teamService = $teamService;
        $this->teamMemberService = $teamMemberService;
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     * @throws ForbiddenRequestException
     * @note teoreticky by sa nemusela už overwritovať
     */
    final protected function configureForm(Form $form): void
    {
        $teamContainer = new ModelContainer($this->container, 'fyziklani_team');
        foreach ($this->getTeamFieldsDefinition() as $field => $metadata) {
            $teamContainer->addField(
                $field,
                $metadata,
                new FieldLevelPermission(FieldLevelPermission::ALLOW_FULL, FieldLevelPermission::ALLOW_FULL)
            );
        }
        $form->addComponent($teamContainer, 'team');
        $this->appendPersonsFields($form);

        if (!isset($this->model)) {
            $privacyControl = $this->reflectionFormFactory->createField('person_info', 'agreed');
            $privacyControl->addRule(Form::FILLED, _('You have to agree with the privacy policy before submitting.'));
            $form->addComponent($privacyControl, 'privacy');
            $form->addComponent(new CaptchaBox(), 'captcha');
        }
    }

    protected function appendPersonsFields(Form $form): void
    {
        $this->appendMemberFields($form);
    }

    /**
     * @throws \Throwable
     */
    final protected function handleFormSuccess(Form $form): void
    {
        $this->teamService->explorer->beginTransaction();
        /** @phpstan-var array{team:array{category:string,name:string}} $values */
        $values = $form->getValues('array');

        try {
            $values = array_reduce(
                $this->getProcessing(),
                fn(array $prevValue, FormProcessing $item): array => $item(
                    $prevValue,
                    $form,
                    $this->event,
                    $this->model
                ),
                $values
            );
            if (isset($values['state'])) { // @phpstan-ignore-line
                throw new InvalidStateException(); // TODO
            }
            $team = $this->teamService->storeModel(
                array_merge($values['team'], [
                    'event_id' => $this->event->event_id,
                ]),
                $this->model
            );
            $this->savePersons($team, $form);
            $holder = $this->machine->createHolder($team);
            if (!isset($this->model)) {
                // ak je nový pošle defaultný mail
                $transition = Machine::selectTransition(Machine::filterAvailable($this->machine->transitions, $holder));
                $this->machine->execute($transition, $holder);
            } elseif (!$this->isOrganizer && $team->state->value !== TeamState::Pending) {
                // nieje čakajúci a nieje to editáci orga pošle to do čakajucich
                $transition = Machine::selectTransition(
                    Machine::filterAvailable(
                        Machine::filterByTarget($this->machine->transitions, TeamState::from(TeamState::Pending)),
                        $holder
                    )
                );
                $this->machine->execute($transition, $holder);
            }
            // pri každej editácii okrem initu pošle mail
            if (isset($this->model) && $this->event->event_type_id === 1) {
                (new TeacherInfoMail($this->container))($holder);
                (new MemberInfoMail($this->container))($holder);
                (new OrganizerInfoMail($this->container))($holder);
            }
            $this->teamService->explorer->commit();
            $this->getPresenter()->flashMessage(
                isset($this->model)
                    ? _('Application has been updated')
                    : _('Application has been created'),
                Message::LVL_SUCCESS
            );
            $this->getPresenter()->redirect('detail', ['id' => $team->fyziklani_team_id]);
        } catch (AbortException $exception) {
            throw $exception;
        } catch (UniqueConstraintViolationException $exception) {
            if (preg_match('/fyziklani_team\.uq_fyziklani_team__name__event/', $exception->getMessage())) {
                $this->flashMessage(_('Team with same name already exists'), Message::LVL_ERROR);
            }
        } catch (DuplicateMemberException | SchoolsPerTeamException $exception) {
            $this->teamService->explorer->rollBack();
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        } catch (\Throwable $exception) {
            $this->teamService->explorer->rollBack();
            throw $exception;
        }
    }

    protected function savePersons(TeamModel2 $team, Form $form): void
    {
        $this->saveMembers($team, $form);
    }

    protected function setDefaults(Form $form): void
    {
        if (isset($this->model)) {
            $form->setDefaults(['team' => $this->model->toArray()]);
            $index = 0;
            /** @var TeamMemberModel $member */
            foreach ($this->model->getMembers() as $member) {
                /** @phpstan-var ReferencedId<PersonModel> $referencedId */
                $referencedId = $form->getComponent('member_' . $index);
                $referencedId->setDefaultValue($member->person);
                $referencedId->searchContainer->setOption(
                    'label',
                    self::formatMemberLabel($index + 1, $member)
                );
                $referencedId->referencedContainer->setOption(
                    'label',
                    self::formatMemberLabel($index + 1, $member)
                );
                $index++;
            }
        }
    }

    protected function saveMembers(TeamModel2 $team, Form $form): void
    {
        $persons = self::getFormMembers($form);
        if (!count($persons)) {
            throw new NoMemberException();
        }
        /** @var TeamMemberModel $oldMember */
        foreach ($team->getMembers()->where('person_id NOT IN', array_keys($persons)) as $oldMember) {
            $this->teamMemberService->disposeModel($oldMember);
        }
        foreach ($persons as $person) {
            $oldMember = $team->getMembers()->where('person_id', $person->person_id)->fetch();
            if (!$oldMember) {
                $this->checkUniqueMember($team, $person);
                $this->teamMemberService->storeModel([
                    'person_id' => $person->getPrimary(),
                    'fyziklani_team_id' => $team->fyziklani_team_id,
                ]);
            }
        }
    }

    protected function checkUniqueMember(TeamModel2 $team, PersonModel $person): void
    {
        /** @var TeamMemberModel $member */
        foreach ($person->getTeamMembers() as $member) {
            if ($member->fyziklani_team_id === $team->fyziklani_team_id) {
                continue;
            }
            if ($member->fyziklani_team->event_id === $this->event->event_id) {
                throw new DuplicateMemberException($person);
            }
        }
    }

    protected function appendMemberFields(Form $form): void
    {
        for ($member = 0; $member < 5; $member++) {
            $memberContainer = $this->referencedPersonFactory->createReferencedPerson(
                $this->getMemberFieldsDefinition(),
                $this->event->getContestYear(),
                'email',
                true,
                new SelfACLResolver(
                    $this->model ?? TeamModel2::RESOURCE_ID,
                    'organizer',
                    $this->event->event_type->contest,
                    $this->container
                ),
                $this->event
            );
            $memberContainer->referencedContainer->collapse = true;
            $memberContainer->searchContainer->setOption('label', self::formatMemberLabel($member + 1));
            $memberContainer->referencedContainer->setOption('label', self::formatMemberLabel($member + 1));
            $form->addComponent($memberContainer, 'member_' . $member);
        }
    }

    /**
     * @phpstan-return EvaluatedFieldsDefinition
     */
    abstract protected function getMemberFieldsDefinition(): array;

    /**
     * @phpstan-return array<string,EvaluatedFieldMetaData>
     */
    abstract protected function getTeamFieldsDefinition(): array;

    /**
     * @phpstan-return FormProcessing[]
     */
    abstract protected function getProcessing(): array;

    /**
     * @phpstan-return PersonModel[]
     */
    public static function getFormMembers(Form $form): array
    {
        $persons = [];
        for ($member = 0; $member < 5; $member++) {
            /** @phpstan-var ReferencedId<PersonModel> $referencedId */
            $referencedId = $form->getComponent('member_' . $member);
            $person = $referencedId->getModel();
            if ($person) {
                $persons[$person->person_id] = $person;
            }
        }
        return $persons;
    }

    public static function formatMemberLabel(int $index, ?TeamMemberModel $member = null): string
    {
        if ($member) {
            return sprintf(_('Member %d - %s'), $index, $member->person->getFullName());
        } else {
            return sprintf(_('Member %d'), $index);
        }
    }
}
