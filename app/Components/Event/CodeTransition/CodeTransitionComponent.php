<?php

declare(strict_types=1);

namespace FKSDB\Components\Event\CodeTransition;

use FKSDB\Components\Controls\FormComponent\CodeForm;
use FKSDB\Components\MachineCode\MachineCode;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\Exceptions\NotImplementedException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Holder\ParticipantHolder;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Transitions\Machine\TeamMachine;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Utils\Html;

class CodeTransitionComponent extends CodeForm
{
    /** @var TeamMachine|EventParticipantMachine<ParticipantHolder> */
    protected Machine $machine;
    /** @var TeamModel2|EventParticipantModel */
    private Model $model;

    /** @var TeamState|EventParticipantStatus */
    private FakeStringEnum $targetState;

    /**
     * @param TeamState|EventParticipantStatus $targetState
     * @param TeamMachine|EventParticipantMachine<ParticipantHolder> $machine
     * @param TeamModel2|EventParticipantModel $model
     */
    public function __construct(
        Container $container,
        Model $model,
        FakeStringEnum $targetState,
        Machine $machine
    ) {
        parent::__construct($container);
        $this->targetState = $targetState;
        $this->model = $model;
        $this->machine = $machine;
    }

    protected function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'layout.latte';
    }

    public function render(): void
    {
        $transitions = Machine::filterByTarget($this->machine->transitions, $this->targetState); //@phpstan-ignore-line
        $holder = $this->machine->createHolder($this->model);
        $this->template->available = (bool)count(Machine::filterAvailable($transitions, $holder));
        parent::render();
    }

    /**
     * @throws BadRequestException
     * @throws NotFoundException
     * @throws \Throwable
     */
    protected function innerHandleSuccess(Model $model): void
    {
        $application = $this->resolveApplication($model);
        if ($model->getPrimary() !== $this->model->getPrimary()) {
            throw new BadRequestException(_('Modely sa nezhodujú')); // TODO
        }
        $holder = $this->machine->createHolder($this->model);
        $transition = Machine::selectTransition(
            Machine::filterAvailable(
                Machine::filterByTarget($this->machine->transitions, $this->targetState), //@phpstan-ignore-line
                $holder
            )
        );
        $this->machine->execute($transition, $holder);//@phpstan-ignore-line
        $this->getPresenter()->flashMessage(
            $application instanceof TeamModel2
                ? sprintf(_('Transition successful for: %s'), $application->name)
                : sprintf(_('Transition successful for: %s'), $application->person->getFullName()),
            Message::LVL_SUCCESS
        );
        $this->getPresenter()->redirect('this');
    }

    /**
     * @return TeamModel2|EventParticipantModel
     * @throws BadRequestException
     * @throws NotFoundException
     */
    private function resolveApplication(Model $model): Model
    {
        if ($model instanceof PersonModel) {
            return $model->getApplication($this->model->event);
        } elseif ($model instanceof EventParticipantModel || $model instanceof TeamModel2) {
            return $model;
        } else {
            throw new BadRequestException(_('Wrong type of code.'));
        }
    }

    protected function configureForm(Form $form): void
    {
        parent::configureForm($form);
        $el = Html::el('span');
        $el->addText(_('Processed: '));
        $transitions = Machine::filterByTarget($this->machine->transitions, $this->targetState);//@phpstan-ignore-line
        foreach ($transitions as $transition) {
            $el->addHtml($transition->source->badge() . '->' . $transition->target->badge());
        }
        $form['code']->setOption('description', $el);//@phpstan-ignore-line
    }

    /**
     * @throws NotImplementedException
     */
    protected function getSalt(): string
    {
        return MachineCode::getSaltForEvent($this->model->event);
    }
}
