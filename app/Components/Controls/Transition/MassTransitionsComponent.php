<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Transition;

use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;

class MassTransitionsComponent extends TransitionComponent
{
    /**
     * @throws BadTypeException
     */
    final public function render(): void
    {
        $this->template->transitions = $this->getMachine()->getTransitions();
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'mass.latte');
    }

    /**
     * @throws BadTypeException
     */
    public function handleTransition(string $name): void
    {
        $total = 0;
        $errored = 0;
        $machine = $this->getMachine();
        $transition = $this->getMachine()->getTransitionById($name);
        if ($this->event->isTeamEvent()) {
            $query = $this->event->getTeams();
        } else {
            $query = $this->event->getParticipants();
        }
        /** @var EventParticipantModel|TeamModel2 $model */
        foreach ($query as $model) {
            $holder = $machine->createHolder($model);
            $total++;
            try {
                $machine->execute($transition, $holder);
            } catch (\Throwable $exception) {
                $errored++;
            }
        }
        $this->getPresenter()->flashMessage(
            sprintf(
                _('Total %d applications, state changed %d, unavailable %d. '),
                $total,
                $total - $errored,
                $errored
            )
        );
        $this->getPresenter()->redirect('this');
    }
}
