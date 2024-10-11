<?php

declare(strict_types=1);

namespace FKSDB\Components\Applications\Team\Forms;

use FKSDB\Components\EntityForms\ModelForm;
use FKSDB\Components\EntityForms\Processing\DefaultTransition;
use FKSDB\Components\EntityForms\Processing\Postprocessing;
use FKSDB\Components\Forms\Containers\ModelContainer;
use FKSDB\Components\Forms\Containers\Models\ReferencedPersonContainer;
use FKSDB\Components\Forms\Controls\CaptchaBox;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Models\Authorization\Resource\EventResourceHolder;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Columns\OmittedControlException;
use FKSDB\Models\ORM\FieldLevelPermission;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamMemberModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\ReflectionFactory;
use FKSDB\Models\ORM\Services\Fyziklani\TeamMemberService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\Persons\Resolvers\SelfEventACLResolver;
use FKSDB\Models\Transitions\Machine\TeamMachine;
use FKSDB\Models\Transitions\TransitionsMachineFactory;
use Fykosak\NetteORM\Model\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\ForbiddenRequestException;
use Nette\Database\UniqueConstraintViolationException;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Http\Request;
use Tracy\Debugger;

/**
 * @phpstan-extends ModelForm<TeamModel2,array{team:array{category:string,name:string}}>
 * @phpstan-import-type EvaluatedFieldMetaData from ReferencedPersonContainer
 * @phpstan-import-type EvaluatedFieldsDefinition from ReferencedPersonContainer
 */
abstract class TeamForm extends ModelForm
{
    protected ReflectionFactory $reflectionFormFactory;
    protected TeamMachine $machine;
    protected ReferencedPersonFactory $referencedPersonFactory;
    protected EventModel $event;
    protected TeamService2 $teamService;
    protected TeamMemberService $teamMemberService;
    protected Request $request;
    protected ?PersonModel $loggedPerson;

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
    final public function injectPrimary(
        Request $request,
        TeamService2 $teamService,
        TeamMemberService $teamMemberService,
        ReflectionFactory $reflectionFormFactory,
        ReferencedPersonFactory $referencedPersonFactory,
        TransitionsMachineFactory $machineFactory
    ): void {
        $this->reflectionFormFactory = $reflectionFormFactory;
        $this->referencedPersonFactory = $referencedPersonFactory;
        $this->teamService = $teamService;
        $this->teamMemberService = $teamMemberService;
        $this->request = $request;
        $this->machine = $machineFactory->getTeamMachine($this->event);
    }

    /**
     * @throws \Throwable
     */
    final protected function innerSuccess(array $values, Form $form): TeamModel2
    {
        $team = $this->teamService->storeModel(
            array_merge($values['team'], [
                'event_id' => $this->event->event_id,
            ]),
            $this->model
        );
        Debugger::log(json_encode([
            'cookies' => $this->request->cookies,
            'headers' => $this->request->headers,
            'remoteAddress' => $this->request->remoteAddress,
            'person' => isset($this->loggedPerson)
                ? ($this->loggedPerson->person_id . ' (' . $this->loggedPerson->getFullName() . ')') :
                '',
            'team' => [
                'fyziklani_team_id' => $team->fyziklani_team_id,
                'name' => $team->name,
                'action' => isset($this->model) ? 'updated' : 'created',
            ],
        ]), 'team-app-remote');
        $this->savePersons($team, $form);
        return $team;
    }

    protected function getPreprocessing(): array
    {
        /** @phpstan-ignore-next-line */
        return [
            function (array $values, Form $form, ?Model $model): array {
                Debugger::log(json_encode([
                    'form' => $values,
                ]), 'team-app-form');
                return $values;
            },
            ... parent::getPreprocessing()
        ];
    }

    protected function successRedirect(Model $model): void
    {
        $this->getPresenter()->flashMessage(
            isset($this->model)
                ? _('Application has been updated')
                : _('Application has been created'),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('detail', ['id' => $model->fyziklani_team_id]);
    }

    protected function onException(\Throwable $exception): bool
    {
        if ($exception instanceof UniqueConstraintViolationException) {
            if (preg_match('/fyziklani_team\.uq_fyziklani_team__name__event/', $exception->getMessage())) {
                $this->flashMessage(_('Team with same name already exists'), Message::LVL_ERROR);
                return true;
            }
        }
        if ($exception instanceof DuplicateMemberException || $exception instanceof NoMemberException) {
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
            return true;
        }
        return parent::onException($exception);
    }

    protected function savePersons(TeamModel2 $team, Form $form): void
    {
        $this->saveMembers($team, $form);
    }

    /**
     * @phpstan-return Postprocessing<TeamModel2>[]
     */
    protected function getPostprocessing(): array
    {
        $processing = parent::getPostprocessing();
        if (!isset($this->model)) {
            $processing[] = new DefaultTransition($this->container, $this->machine); //@phpstan-ignore-line
        }
        return $processing; //@phpstan-ignore-line
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
     * @phpstan-return array<string,EvaluatedFieldMetaData>
     */
    abstract protected function getTeamFieldsDefinition(): array;

    /**
     * @phpstan-return EvaluatedFieldsDefinition
     */
    abstract protected function getMemberFieldsDefinition(): array;

    protected function appendMemberFields(Form $form): void
    {
        for ($member = 0; $member < 5; $member++) {
            $memberContainer = $this->referencedPersonFactory->createReferencedPerson(
                $this->getMemberFieldsDefinition(),
                $this->event->getContestYear(),
                'email',
                true,
                new SelfEventACLResolver(
                    $this->model
                        ? EventResourceHolder::fromOwnResource($this->model)
                        : EventResourceHolder::fromResourceId(TeamModel2::RESOURCE_ID, $this->event),
                    'organizer',
                    $this->event,
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
