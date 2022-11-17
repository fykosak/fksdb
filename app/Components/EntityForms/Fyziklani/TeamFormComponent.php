<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Components\Forms\Controls\CaptchaBox;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Components\Forms\FormProcessing\FormProcessing;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\Fyziklani\TeamMemberService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\ORM\Services\Fyziklani\TeamTeacherService;
use FKSDB\Models\Persons\Resolvers\SelfACLResolver;
use FKSDB\Models\Transitions\Machine\FyziklaniTeamMachine;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\AbortException;
use Nette\DI\Container;
use Nette\Forms\Form;
use Tracy\Debugger;

/**
 * @property TeamModel2 $model
 */
abstract class TeamFormComponent extends EntityFormComponent
{
    protected SingleReflectionFormFactory $reflectionFormFactory;
    protected FyziklaniTeamMachine $machine;
    protected ReferencedPersonFactory $referencedPersonFactory;
    protected EventModel $event;
    protected TeamService2 $teamService;
    protected TeamMemberService $teamMemberService;
    protected TeamTeacherService $teacherService;

    public function __construct(
        FyziklaniTeamMachine $machine,
        EventModel $event,
        Container $container,
        ?Model $model
    ) {
        parent::__construct($container, $model);
        $this->machine = $machine;
        $this->event = $event;
    }

    final public function injectPrimary(
        TeamService2 $teamService,
        TeamMemberService $teamMemberService,
        TeamTeacherService $teacherService,
        SingleReflectionFormFactory $reflectionFormFactory,
        ReferencedPersonFactory $referencedPersonFactory
    ): void {
        $this->reflectionFormFactory = $reflectionFormFactory;
        $this->referencedPersonFactory = $referencedPersonFactory;
        $this->teamService = $teamService;
        $this->teamMemberService = $teamMemberService;
        $this->teacherService = $teacherService;
    }

    /**
     * @throws \Throwable
     */
    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues('array');
        $this->teamService->explorer->beginTransaction();
        try {
            $values = array_reduce(
                $this->getProcessing(),
                fn(array $prevValue, FormProcessing $item): array => $item($prevValue, $form, $this->event),
                $values
            );
            $this->checkUniqueTeamName($values['team']['name']);

            // $newState = $values['team']['state'] ?? null;
            // unset($values['team']['state']);

            $team = $this->teamService->storeModel(
                array_merge($values['team'], [
                    'event_id' => $this->event->event_id,
                ]),
                $this->model
            );
            $this->saveTeachers($team, $form);

            $this->saveTeamMembers($team, $form);
            if (!isset($this->model)) {
                $holder = $this->machine->createHolder($team);
                $this->machine->executeImplicitTransition($holder);
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
        } catch (DuplicateTeamNameException | DuplicateMemberException | TooManySchoolsException $exception) {
            $this->teamService->explorer->rollBack();
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        } catch (\Throwable $exception) {
            $this->teamService->explorer->rollBack();
            throw $exception;
        }
    }

    protected function saveTeamMembers(TeamModel2 $team, Form $form): void
    {
        $persons = self::getMembersFromForm($form);
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

    protected function saveTeachers(TeamModel2 $team, Form $form): void
    {
        $persons = self::getTeacherFromForm($form);

        $oldMemberQuery = $team->getTeachers();
        if (count($persons)) {
            $oldMemberQuery->where('person_id NOT IN', array_keys($persons));
        }
        /** @var TeamMemberModel $oldTeacher */
        foreach ($oldMemberQuery as $oldTeacher) {
            $this->teacherService->disposeModel($oldTeacher);
        }
        foreach ($persons as $person) {
            $oldTeacher = $team->getTeachers()->where('person_id', $person->person_id)->fetch();
            if (!$oldTeacher) {
                $this->teacherService->storeModel([
                    'person_id' => $person->getPrimary(),
                    'fyziklani_team_id' => $team->fyziklani_team_id,
                ]);
            }
        }
    }

    /**
     * @return PersonModel[]
     */
    public static function getMembersFromForm(Form $form): array
    {
        $persons = [];
        for ($member = 0; $member < 5; $member++) {
            /** @var ReferencedId $referencedId */
            $referencedId = $form->getComponent('member_' . $member);
            /** @var PersonModel $person */
            $person = $referencedId->getModel();
            if ($person) {
                $persons[$person->person_id] = $person;
            }
        }
        return $persons;
    }

    /**
     * @return PersonModel[]
     */
    public static function getTeacherFromForm(Form $form): array
    {
        /** @var ReferencedId $referencedId */
        $referencedId = $form->getComponent('teacher');
        /** @var PersonModel $person */
        $person = $referencedId->getModel();
        return $person ? [$person->person_id => $person] : [];
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

    protected function checkUniqueTeamName(string $name): void
    {
        $query = $this->teamService->getTable()
            ->where('event_id', $this->event->event_id)
            ->where('name', $name);
        if (isset($this->model)) {
            $query->where('fyziklani_team_id != ?', $this->model->fyziklani_team_id);
        }
        if ($query->fetch()) {
            throw new DuplicateTeamNameException($name);
        }
    }

    /**
     * @throws BadTypeException
     */
    protected function setDefaults(): void
    {
        if (isset($this->model)) {
            $this->getForm()->setDefaults(['team' => $this->model->toArray()]);
            /** @var TeamMemberModel $member */
            $index = 0;
            foreach ($this->model->getMembers() as $member) {
                /** @var ReferencedId $referencedId */
                $referencedId = $this->getForm()->getComponent('member_' . $index);
                $referencedId->setDefaultValue($member->person);
                $index++;
            }
            $teacher = $this->model->getTeachers()->fetch();
            if ($teacher) {
                /** @var ReferencedId $referencedId */
                $referencedId = $this->getForm()->getComponent('teacher');
                $referencedId->setDefaultValue($member->person);
            }
        }
    }

    protected function appendTeacherField(Form $form): void
    {
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
                    $this->model ? 'org-edit' : 'org-create',
                    $this->event->event_type->contest,
                    $this->container
                ),
                $this->event
            );
            $memberContainer->searchContainer->setOption('label', sprintf(_('Member #%d'), $member + 1));
            $memberContainer->referencedContainer->setOption('label', sprintf(_('Member #%d'), $member + 1));
            $form->addComponent($memberContainer, 'member_' . $member);
        }
    }

    abstract protected function getTeamFields(): array;

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void
    {
        $teamContainer = $this->reflectionFormFactory->createContainer(
            'fyziklani_team',
            $this->getTeamFields()
        );
        $form->addComponent($teamContainer, 'team');

        $this->appendTeacherField($form);
        $this->appendMemberFields($form);

        $privacyControl = $this->reflectionFormFactory->createField('person_info', 'agreed');
        $privacyControl->addRule(Form::FILLED, _('You have to agree with the privacy policy before submitting.'));
        $form->addComponent($privacyControl, 'privacy');
        $form->addComponent(new CaptchaBox(), 'captcha');
    }

    abstract protected function getMemberFieldsDefinition(): array;

    abstract protected function getTeacherFieldsDefinition(): array;

    abstract protected function getProcessing(): array;
}
