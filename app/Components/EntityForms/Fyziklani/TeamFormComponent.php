<?php

declare(strict_types=1);

namespace FKSDB\Components\EntityForms\Fyziklani;

use FKSDB\Components\EntityForms\EntityFormComponent;
use FKSDB\Components\Forms\Factories\ReferencedPerson\ReferencedPersonFactory;
use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Components\Forms\FormAdjustment;
use FKSDB\Components\Forms\FormProcessing;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\OmittedControlException;
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
        SingleReflectionFormFactory $reflectionFormFactory,
        ReferencedPersonFactory $referencedPersonFactory,
        User $user
    ): void {
        $this->reflectionFormFactory = $reflectionFormFactory;
        $this->referencedPersonFactory = $referencedPersonFactory;
        $this->user = $user;
    }

    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues('array');
        $holder = $this->machine->createHolder($this->model ?? null);

        $values = array_reduce(
            $this->getProcessing(),
            fn(array $prevValue, FormProcessing $item): array => $item->process($prevValue, $holder),
            $values
        );
        $adjustments = $this->getAdjustments();
        array_walk(
            $adjustments,
            fn(FormAdjustment $item) => $item->adjust($values, $holder)
        );
    }


    protected function setDefaults(): void
    {
        // TODO: Implement setDefaults() method.
    }

    /**
     * @param Form $form
     * @throws BadTypeException
     * @throws OmittedControlException
     */
    protected function configureForm(Form $form): void
    {
        $teamContainer = $this->reflectionFormFactory->createContainer('fyziklani_team', ['name', 'password']);
        $form->addComponent($teamContainer, 'team');
        for ($member = 0; $member < 5; $member++) {
            $memberContainer = $this->referencedPersonFactory->createReferencedPerson(
                $this->getFieldsDefinition(),
                $this->event->getContestYear(),
                'email',
                $member !== 0,
                new SelfResolver($this->user),
                new SelfResolver($this->user),
                $this->event
            );
            $teamContainer->addComponent($memberContainer, 'member_' . $member);
        }
    }

    abstract protected function getFieldsDefinition(): array;

    abstract protected function getProcessing(): array;

    abstract protected function getAdjustments(): array;
}
