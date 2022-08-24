<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Components\Forms\Controls\ReferencedId;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Components\Forms\FormAdjustment\FormAdjustment;
use FKSDB\Components\Forms\FormProcessing\FormProcessing;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\OmittedControlException;
use FKSDB\Models\ORM\Services\Fyziklani\TeamMemberService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use FKSDB\Models\Persons\SelfResolver;
use FKSDB\Models\Transitions\Machine\FyziklaniTeamMachine;
use Fykosak\NetteORM\Model;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Security\User;

abstract class TeamFormComponent extends EntityFormComponent
{
    private SingleReflectionFormFactory $reflectionFormFactory;
    private FyziklaniTeamMachine $machine;
    private ReferencedPersonFactory $referencedPersonFactory;
    private EventModel $event;
    private User $user;
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
        ReferencedPersonFactory $referencedPersonFactory,
        User $user
    ): void {
        $this->reflectionFormFactory = $reflectionFormFactory;
        $this->referencedPersonFactory = $referencedPersonFactory;
        $this->user = $user;
        $this->teamService = $teamService;
        $this->teamMemberService = $teamMemberService;
    }

    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues('array');
        $holder = $this->machine->createHolder($this->model ?? null);

        $values = array_reduce(
            $this->getProcessing(),
            fn(array $prevValue, FormProcessing $item): array => $item($prevValue, $form, $this->event, $holder),
            $values
        );
        $adjustments = $this->getAdjustments();
        array_walk(
            $adjustments,
            fn(FormAdjustment $item) => $item($values, $form, $this->event, $holder)
        );
        if (!isset($this->model)) {
            $team = $this->teamService->storeModel(
                array_merge($values['team'], [
                    'event_id' => $this->event->event_id,
                ])
            );
            for ($member = 0; $member < 5; $member++) {
                /** @var ReferencedId $referencedId */
                $referencedId = $form->getComponent('member_' . $member);
                $person = $referencedId->getModel();
                if ($person) {
                    $data = [
                        'person_id' => $person->getPrimary(),
                        'fyziklani_team_id' => $team->fyziklani_team_id,
                    ];
                    $this->teamMemberService->storeModel($data);
                }
            }
            $holder = $this->machine->createHolder($team);
            $this->machine->executeImplicitTransition($holder);
        }
    }

    protected function setDefaults(): void
    {
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
                new SelfResolver($this->user),
                new SelfResolver($this->user),
                $this->event
            );
            $memberContainer->getSearchContainer()->setOption('label', sprintf(_('Member #%d'), $member + 1));
            $memberContainer->getReferencedContainer()->setOption('label', sprintf(_('Member #%d'), $member + 1));
            $form->addComponent($memberContainer, 'member_' . $member);
        }
    }

    abstract protected function getFieldsDefinition(): array;

    abstract protected function getProcessing(): array;

    abstract protected function getAdjustments(): array;
}
