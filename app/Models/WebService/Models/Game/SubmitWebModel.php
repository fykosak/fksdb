<?php

declare(strict_types=1);

namespace FKSDB\Models\WebService\Models\Game;

use FKSDB\Components\Game\GameException;
use FKSDB\Components\Game\Submits\TaskCodePreprocessor;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\EventService;
use FKSDB\Models\WebService\Models\WebModel;
use Fykosak\Utils\Logging\Message;
use Nette\Application\BadRequestException;
use Nette\Schema\Elements\Structure;
use Nette\Schema\Expect;

/**
 * @phpstan-extends WebModel<array<string,mixed>,array<string,mixed>>
 */
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
            'method' => Expect::anyOf('create', 'check', 'edit', 'revoke')->required(),
            'event_id' => Expect::scalar()->castTo('int')->required(),
            'code' => Expect::string()->pattern('^[0-9]{4,6}[a-hA-H]{2}[0-9]$')->required(),
            'points' => Expect::scalar()->castTo('int'),
        ]);
    }

    /**
     * @param array{
     *     method:string,
     *     event_id:int,
     *     code:string,
     *     points:int,
     * } $params
     * @phpstan-return Message[]
     */
    public function getJsonResponse(array $params): array
    {
        try {
            /** @var EventModel|null $event */
            $event = $this->eventService->findByPrimary($params['event_id']);
            if (!$event) {
                throw new BadRequestException();
            }
            $handler = $event->createGameHandler($this->container);
            $codeProcessor = new TaskCodePreprocessor($event);
            $team = $codeProcessor->getTeam($params['code']);
            $task = $codeProcessor->getTask($params['code']);
            $points = $params['points'];
            switch ($params['method']) {
                case 'create':
                    $handler->create($task, $team, $points);
                    break;
                case 'check':
                    $submit = $team->getSubmit($task);
                    if (!$submit) {
                        throw new BadRequestException();
                    }
                    $handler->check($submit, $points);
                    break;
                case 'edit':
                    $submit = $team->getSubmit($task);
                    if (!$submit) {
                        throw new BadRequestException();
                    }
                    $handler->edit($submit, $points);
                    break;
                case 'revoke':
                    $submit = $team->getSubmit($task);
                    if (!$submit) {
                        throw new BadRequestException();
                    }
                    $handler->revoke($submit);
                    break;
                default:
                    throw new BadRequestException();
            }
            return $handler->logger->getMessages();
        } catch (GameException | BadRequestException $exception) {
            return [
                new Message($exception->getMessage(), Message::LVL_ERROR),
            ];
        } catch (\Throwable $exception) {
            return [
                new Message(_('Undefined error'), Message::LVL_ERROR),
            ];
        }
    }
}
