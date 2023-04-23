<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Fyziklani;

use FKSDB\Components\Game\Submits\TaskCodeException;
use FKSDB\Components\Game\Submits\TaskCodePreprocessor;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\WebService\Models\WebModel;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

class SubmitWebModel extends WebModel
{
    protected EventService $eventService;

    public function inject(EventService $eventService): void
    {
        $this->eventService = $eventService;
    }

    public function getExpectedParams(): Structure
    {
        return Expect::structure([
            'action' => Expect::anyOf('submit', 'edit', 'revoke')->required(),
            'event_id' => Expect::scalar()->castTo('int')->required(),
            'code' => Expect::string()->pattern('[0-9]{4}[A-H]{2}[0-9]{1}')->required(),
            'points' => Expect::scalar()->castTo('int'),
        ]);
    }

    public function getJsonResponse(array $params): array
    {
        try {
            /** @var EventModel $event */
            $event = $this->eventService->findByPrimary($params['event_id']);
            if (!$event) {
                throw new BadRequestException();
            }
            $handler = $event->createGameHandler($this->container);
            $team = TaskCodePreprocessor::getTeam($params['code'], $event);
            $task = TaskCodePreprocessor::getTask($params['code'], $event);
            switch ($params['action']) {
                case 'submit':
                    $handler->handle($team, $task, $params['points']);
                    break;
                case 'edit':
                    $submit = $team->getSubmit($task);
                    if (!$submit) {
                        throw new BadRequestException();
                    }
                    $handler->edit($submit, $params['points']);
                    break;
                case 'revoke':
                    $submit = $team->getSubmit($task);
                    if (!$submit) {
                        throw new BadRequestException();
                    }
                    $handler->revoke($submit);
            }
            return $handler->logger->getMessages();
        } catch (TaskCodeException | BadRequestException$exception) {
            return [
                new Message($exception->getMessage(), Message::LVL_ERROR),
            ];
        }
    }
}
