<?php

namespace FKSDB\Components\Controls\Entity;

use FKSDB\Components\Forms\Factories\SingleReflectionFormFactory;
use FKSDB\Models\TeamApplications\TeamApplicationHolder;
use FKSDB\Models\TeamApplications\TeamMachine;
use Fykosak\NetteORM\AbstractModel;
use Nette\DI\Container;
use Nette\Forms\Form;

class TeamApplicationFormComponent extends AbstractEntityFormComponent {

    private SingleReflectionFormFactory $reflectionFormFactory;
    private TeamMachine $machine;
    private array $adjustment;

    public function __construct(TeamMachine $machine, Container $container, ?AbstractModel $model) {
        parent::__construct($container, $model);
        $this->machine = $machine;
        $this->adjustment = [
            function (TeamApplicationHolder $holder, array $values): void {
            },

        ];
    }

    protected function handleFormSuccess(Form $form): void {
        $values = $form->getValues('array');
        $holder = $this->machine->createHolder($this->model ?? null);
        $values = array_reduce($this->adjustment, function ($prevValue, $item) {
            return $item($this, $prevValue);
        }, $values);
    }

    final public function injectPrimary(SingleReflectionFormFactory $reflectionFormFactory): void {
        $this->reflectionFormFactory = $reflectionFormFactory;
    }

    protected function setDefaults(): void {
        // TODO: Implement setDefaults() method.
    }

    protected function configureForm(Form $form): void {
        $teamContainer = $this->reflectionFormFactory->createContainer('e_fyziklani_team', ['name', 'password']);
        $form->addComponent($teamContainer, 'team');
    }
}
