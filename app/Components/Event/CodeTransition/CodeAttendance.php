<?php

declare(strict_types=1);

namespace FKSDB\Components\Event\CodeTransition;

use FKSDB\Components\Transitions\Code\CodeTransition;
use FKSDB\Models\MachineCode\MachineCodeException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Machine\Machine;
use FKSDB\Models\Utils\FakeStringEnum;
use Fykosak\NetteORM\Model\Model;
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
final class CodeAttendance extends CodeTransition
{
    /** @phpstan-var TeamModel2|EventParticipantModel */
    private Model $model;

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
        parent::__construct($container, $targetState, $machine);
        $this->model = $model;
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
                $this->getTransitions(),
                $holder
            )
        );
        return $hasTransition && $this->model->createMachineCode();
    }

    protected function configureForm(Form $form): void
    {
        parent::configureForm($form);
        $el = Html::el('span');
        $el->addText(_('Processed') . ': ');
        $transitions = Machine::filterByTarget($this->machine->transitions, $this->targetState);
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

    /**
     * @throws BadRequestException
     */
    protected function resolveModel(Model $model): Model
    {
        if ($model instanceof TeamModel2) {
            $application = $model;
        } elseif ($model instanceof PersonModel) {
            $application = $model->getEventParticipant($this->model->event);
        } else {
            throw new BadRequestException(_('Wrong type of code.'));
        }
        if (!$application || $application->getPrimary() !== $this->model->getPrimary()) {
            throw new BadRequestException(_('Modely sa nezhodujú')); // TODO
        }
        return $model;
    }
}
