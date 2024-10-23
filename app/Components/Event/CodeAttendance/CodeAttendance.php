<?php

declare(strict_types=1);

namespace FKSDB\Components\Event\CodeAttendance;

use FKSDB\Components\Transitions\Code\CodeTransition;
use FKSDB\Models\MachineCode\MachineCodeException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\EventParticipantStatus;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\ORM\Models\Fyziklani\TeamState;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\Transitions\Machine\EventParticipantMachine;
use FKSDB\Models\Transitions\Machine\TeamMachine;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\Forms\Form;
use Nette\Utils\Html;

/**
 * @phpstan-extends CodeTransition<TeamModel2|EventParticipantModel>
 */
final class CodeAttendance extends CodeTransition
{
    private EventParticipantModel|TeamModel2 $model;

    public function __construct(
        Container $container,
        TeamModel2|EventParticipantModel $model,
        TeamState|EventParticipantStatus $targetState,
        TeamMachine|EventParticipantMachine $machine
    ) {
        /** @phpstan-ignore-next-line */
        parent::__construct($container, $targetState, $machine);
        $this->model = $model;
    }

    public function available(): bool
    {
        $holder = $this->machine->createHolder($this->model);
        $hasTransition = $this->getTransitions()->filterAvailable($holder)->count();
        return $hasTransition && $this->model->createMachineCode();
    }

    protected function finalRedirect(): void
    {
        $this->getPresenter()->redirect('search', ['id' => null]);
    }

    protected function configureForm(Form $form): void
    {
        parent::configureForm($form);
        $el = Html::el('span');
        $el->addText(_('Processed') . ': ');
        $transitions = $this->machine->getTransitions()->filterByTarget($this->targetState)->toArray();
        foreach ($transitions as $transition) {
            $el->addHtml($transition->source->badge() . '->' . $transition->target->badge());
        }
        $form['code']->setOption('description', $el);//@phpstan-ignore-line
    }

    /**
     * @throws BadRequestException
     */
    protected function resolveModel(PersonModel|TeamModel2 $model): EventParticipantModel|TeamModel2
    {
        if ($model instanceof TeamModel2) {
            $application = $model;
        } else {
            $application = $model->getEventParticipant($this->model->event);
        }
        if (!$application || $application->getPrimary() !== $this->model->getPrimary()) {
            throw new BadRequestException(_('Models do not match')); // TODO
        }
        return $application;
    }

    /**
     * @throws MachineCodeException
     */
    protected function getSalt(): string
    {
        return $this->model->event->getSalt();
    }
}
