<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits\Form;

use FKSDB\Components\Game\Submits\ClosedSubmittingException;
use FKSDB\Components\Game\Submits\TaskCodePreprocessor;
use FKSDB\Models\ORM\Models\EventModel;
use Nette\DI\Container;

class CtyrbojPointsEntryComponent extends PointsEntryComponent
{

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container, $event, 'ctyrboj.submit-form');
    }

    /**
     * @throws ClosedSubmittingException
     */
    protected function innerHandleSave(array $data): void
    {
        $task = TaskCodePreprocessor::getTask($data['code'], $this->event);
        $team = TaskCodePreprocessor::getTeam($data['code'], $this->event);

        $handler = $this->event->createGameHandler($this->getContext());
        $handler->handle($team, $task, null);
        foreach ($handler->logger->getMessages() as $message) {
            $this->getLogger()->log($message);
        }
    }
}
