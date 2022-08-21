<?php

namespace FKSDB\Components\EntityForms;

use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\TeamApplications\TeamMachine;
use FKSDB\Models\Transitions\FormAdjustment\FormAdjustment;
use Fykosak\NetteORM\AbstractModel;
use Nette\DI\Container;
use Nette\Forms\Form;

class TeamApplicationFormComponent extends AbstractEntityFormComponent
{

    private SingleReflectionFormFactory $reflectionFormFactory;
    private TeamMachine $machine;
    /** @var FormAdjustment[] */
    private array $adjustment;

    public function __construct(TeamMachine $machine, Container $container, ?AbstractModel $model)
    {
        parent::__construct($container, $model);
        $this->machine = $machine;
        $this->adjustment = [];
    }

    protected function handleFormSuccess(Form $form): void
    {
        $values = $form->getValues('array');
        $holder = $this->machine->createHolder($this->model ?? null);
        $values = array_reduce(
            $this->adjustment,
            fn(array $prevValue, FormAdjustment $item): array => $item->adjust($prevValue, $holder),
            $values
        );
    }

    final public function injectPrimary(SingleReflectionFormFactory $reflectionFormFactory): void
    {
        $this->reflectionFormFactory = $reflectionFormFactory;
    }

    protected function setDefaults(): void
    {
        // TODO: Implement setDefaults() method.
    }

    protected function configureForm(Form $form): void
    {
        $teamContainer = $this->reflectionFormFactory->createContainer('e_fyziklani_team', ['name', 'password']);
        $form->addComponent($teamContainer, 'team');
    }
}
