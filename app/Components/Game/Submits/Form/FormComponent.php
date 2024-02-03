<?php

declare(strict_types=1);

namespace FKSDB\Components\Game\Submits\Form;

use FKSDB\Components\Game\GameException;
use FKSDB\Components\Game\Submits\TaskCodePreprocessor;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\Fyziklani\TaskService;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use Fykosak\NetteFrontendComponent\Components\AjaxComponent;
use Fykosak\Utils\Logging\Message;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;

class FormComponent extends AjaxComponent
{
    protected EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container, $event->event_type_id === 1 ? 'fyziklani.submit-form' : 'ctyrboj.submit-form');
        $this->event = $event;
    }

    /**
     * @phpstan-return array{
     *     tasks:array<int,mixed>,
     *     teams:array<int,mixed>,
     *     availablePoints:int[],
     * }
     */
    protected function getData(): array
    {
        return [
            'tasks' => TaskService::serialiseTasks($this->event),
            'teams' => TeamService2::serialiseTeams($this->event),
            'availablePoints' => $this->event->getGameSetup()->getAvailablePoints(),
        ];
    }

    /**
     * @throws InvalidLinkException
     */
    protected function configure(): void
    {
        $this->addAction('save', 'save!');
    }

    public function handleSave(): void
    {
        $data = (array)json_decode($this->getHttpRequest()->getRawBody());
        $codeProcessor = new TaskCodePreprocessor($this->event);
        try {
            $code = strtoupper($data['code']);
            $task = $codeProcessor->getTask($code);
            $team = $codeProcessor->getTeam($code);
            $handler = $this->event->createGameHandler($this->getContext());
            $handler->handle($team, $task, $data['points'] ? (int)$data['points'] : null);
            foreach ($handler->logger->getMessages() as $message) {
                $this->getLogger()->log($message);
            }
        } catch (GameException $exception) {
            $this->getLogger()->log(new Message($exception->getMessage(), Message::LVL_ERROR));
        }
        $this->sendAjaxResponse();
    }
}
