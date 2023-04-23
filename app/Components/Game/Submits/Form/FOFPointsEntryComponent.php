<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits\Form;

use FKSDB\Components\Game\NotSetGameParametersException;
use FKSDB\Components\Game\Submits\ClosedSubmittingException;
use FKSDB\Components\Game\Submits\TaskCodePreprocessor;
use FKSDB\Models\ORM\Models\EventModel;
use Nette\DI\Container;

class FOFPointsEntryComponent extends PointsEntryComponent
{
    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container, $event, 'fyziklani.submit-form');
    }

    /**
     * @throws NotSetGameParametersException
     */
    protected function getData(): array
    {
        $data = parent::getData();
        $data['availablePoints'] = $this->event->getGameSetup()->getAvailablePoints();
        return $data;
    }

    /**
     * @throws ClosedSubmittingException
     */
    public function innerHandleSave(array $data): void
    {
        $task = TaskCodePreprocessor::getTask($data['code'], $this->event);
        $team = TaskCodePreprocessor::getTeam($data['code'], $this->event);

        $handler = $this->event->createGameHandler($this->getContext());
        $handler->handle($team, $task, +$data['points']);
        foreach ($handler->logger->getMessages() as $message) {
            $this->getLogger()->log($message);
        }
    }
}
