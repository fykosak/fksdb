<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use FKSDB\Components\EntityForms\EntityFormComponent;
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
    private SingleReflectionFormFactory $reflectionFormFactory;
    private FyziklaniTeamMachine $machine;
    private ReferencedPersonFactory $referencedPersonFactory;
    private EventModel $event;
    private TeamService2 $teamService;
    private TeamMemberService $teamMemberService;

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
        SingleReflectionFormFactory $reflectionFormFactory,
        ReferencedPersonFactory $referencedPersonFactory
    ): void {
        $this->reflectionFormFactory = $reflectionFormFactory;
        $this->referencedPersonFactory = $referencedPersonFactory;
        $this->teamService = $teamService;
        $this->teamMemberService = $teamMemberService;
    }

    /**
     * @throws \Throwable
     */
    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues('array');
        Debugger::barDump($values);
        $this->teamService->explorer->beginTransaction();
        try {
            $values = array_reduce(
                $this->getProcessing(),
                fn(array $prevValue, FormProcessing $item): array => $item($prevValue, $form, $this->event),
                $values
            );
            $this->checkUniqueTeamName($values['team']['name']);
            $team = $this->teamService->storeModel(
                array_merge($values['team'], [
                    'event_id' => $this->event->event_id,
                ]),
                $this->model
            );

            $this->saveTeamMembers($team, $form);

            if (!isset($this->model)) {
                $holder = $this->machine->createHolder($team);
                $this->machine->executeImplicitTransition($holder);
            }
            $this->teamService->explorer->commit();
            $this->flashMessage(
                isset($this->model)
                    ? _('Application has been updated')
                    : _('Application has been create'),
                Message::LVL_SUCCESS
            );
            $this->getPresenter()->redirect('detail', ['id' => $team->fyziklani_team_id]);
        } catch (AbortException $exception) {
            throw $exception;
        } catch (DuplicateTeamNameException | DuplicateMemberException $exception) {
            $this->teamService->explorer->rollBack();
            $this->flashMessage($exception->getMessage(), Message::LVL_ERROR);
        } catch (\Throwable $exception) {
            $this->teamService->explorer->rollBack();
            throw $exception;
        }
    }

    protected function saveTeamMembers(TeamModel2 $team, Form $form): void
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
        /** @var TeamMemberModel $oldMember */
        foreach ($team->getMembers()->where('person_id NOT IN', array_keys($persons)) as $oldMember) {
            $this->teamMemberService->disposeModel($oldMember);
        }
        foreach ($persons as $person) {
            $oldTeamMember = $team->getMembers()->where('person_id', $person->person_id)->fetch();
            if (!$oldTeamMember) {
                $this->checkUniqueMember($team, $person);
                $data = [
                    'person_id' => $person->getPrimary(),
                    'fyziklani_team_id' => $team->fyziklani_team_id,
                ];
                $this->teamMemberService->storeModel($data);
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
            }
        }
    }

    /**
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void
    {
        $teamContainer = $this->reflectionFormFactory->createContainer(
            'fyziklani_team',
            ['name']
        );
        $form->addComponent($teamContainer, 'team');
        for ($member = 0; $member < 5; $member++) {
            $memberContainer = $this->referencedPersonFactory->createReferencedPerson(
                $this->getFieldsDefinition(),
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

    abstract protected function getFieldsDefinition(): array;

    abstract protected function getProcessing(): array;
}

/* TODO
                - @events.captcha
                - FKSDB\Models\Events\Spec\Fol\FlagCheck()
                - FKSDB\Models\Events\Spec\Fol\BornCheck()

            processings:
                - @events.privacyPolicy
 */
