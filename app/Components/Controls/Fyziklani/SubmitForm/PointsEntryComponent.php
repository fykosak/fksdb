<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Fyziklani\Submit;

use FKSDB\Models\Fyziklani\Submit\ClosedSubmittingException;
use FKSDB\Models\Fyziklani\Submit\TaskCodeException;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use Fykosak\NetteFrontendComponent\Components\AjaxComponent;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\Fyziklani\TaskService;
use Fykosak\Utils\Logging\Message;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;

abstract class PointsEntryComponent extends AjaxComponent
{
    protected EventModel $event;

    public function __construct(Container $container, EventModel $event, string $frontendId)
    {
        parent::__construct($container, $frontendId);
        $this->event = $event;
    }

    protected function getData(): array
    {
        return [
            'tasks' => TaskService::serialiseTasks($this->event),
            'teams' => TeamService2::serialiseTeams($this->event),
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
        try {
            $this->innerHandleSave($data);
        } catch (TaskCodeException | ClosedSubmittingException $exception) {
            $this->getLogger()->log(new Message($exception->getMessage(), Message::LVL_ERROR));
        }
        $this->sendAjaxResponse();
    }

    /**
     * @throws ClosedSubmittingException
     */
    abstract protected function innerHandleSave(array $data): void;
}
