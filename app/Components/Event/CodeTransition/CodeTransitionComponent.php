<?php

declare(strict_types=1);

namespace FKSDB\Components\Event\CodeTransition;

use FKSDB\Components\Controls\FormComponent\CodeForm;
use FKSDB\Models\Exceptions\NotFoundException;
use FKSDB\Models\MachineCode\MachineCodeException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * @phpstan-template TModel of TeamModel2|EventParticipantModel
 * @phpstan-type TState (TModel is TeamModel2
 *     ?\FKSDB\Models\ORM\Models\Fyziklani\TeamState
 *     :\FKSDB\Models\ORM\Models\EventParticipantStatus)
 * @phpstan-type TMachine (TModel is TeamModel2
 *     ?\FKSDB\Models\Transitions\Machine\TeamMachine
 *     :\FKSDB\Models\Transitions\Machine\EventParticipantMachine<\FKSDB\Models\Transitions\Holder\ParticipantHolder>)
 */
final class CodeTransitionComponent extends CodeForm
{
    /** @phpstan-var TMachine */
    protected Machine $machine;
    /** @phpstan-var TModel */
    private Model $model;

    /** @phpstan-var TState */
    private FakeStringEnum $targetState;

    /**
     * @phpstan-param TState $targetState
     * @phpstan-param TMachine $machine
     * @phpstan-param TModel $model
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

    public function available(): bool
    {
        $holder = $this->machine->createHolder($this->model);
        $hasTransition = count(
            Machine::filterAvailable(
                Machine::filterByTarget($this->machine->transitions, $this->targetState), //@phpstan-ignore-line
                $holder
            )
        );
        return $hasTransition && $this->model->createMachineCode();
    }

    /**
     * @throws BadRequestException
     * @throws NotFoundException
     * @throws \Throwable
     */
    protected function innerHandleSuccess(Model $model, Form $form): void
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
     * @throws MachineCodeException
     */
    protected function getSalt(): string
    {
        return $this->model->event->getSalt();
    }
}
