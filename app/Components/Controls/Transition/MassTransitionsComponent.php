<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Transition;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Fyziklani\TeamModel2;
use FKSDB\Models\Transitions\Machine\Machine;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;

/**
 * @phpstan-template TMachine of Machine
 */
class MassTransitionsComponent extends BaseComponent
{
    /** @phpstan-var TMachine */
    protected Machine $machine;
    private EventModel $event;

    /**
     * @phpstan-param TMachine $machine
     */
    public function __construct(Container $container, Machine $machine, EventModel $event)
    {
        parent::__construct($container);
        $this->event = $event;
        $this->machine = $machine;
    }

    final public function render(): void
    {
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'mass.latte', [
            'transitions' => $this->machine->getTransitions(),
        ]);
    }

    public function handleTransition(string $name): void
    {
        $total = 0;
        $errored = 0;
        $transition = $this->machine->getTransitionById($name);
        if ($this->event->isTeamEvent()) {
            $query = $this->event->getTeams();
        } else {
            $query = $this->event->getParticipants();
        }
        /** @var EventParticipantModel|TeamModel2 $model */
        foreach ($query as $model) {
            $holder = $this->machine->createHolder($model);
            $total++;
            try {
                $this->machine->execute($transition, $holder);
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
