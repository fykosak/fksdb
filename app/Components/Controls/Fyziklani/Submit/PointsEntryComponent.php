<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Fyziklani\Submit;

use FKSDB\Models\Fyziklani\Submit\ClosedSubmittingException;
use FKSDB\Models\Fyziklani\Submit\Handler;
use FKSDB\Models\ORM\Services\Fyziklani\TeamService2;
use Fykosak\NetteFrontendComponent\Components\AjaxComponent;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\Fyziklani\NotSetGameParametersException;
use FKSDB\Models\Fyziklani\Submit\TaskCodeException;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Services\Fyziklani\TaskService;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;

class PointsEntryComponent extends AjaxComponent
{
    private EventModel $event;

    public function __construct(Container $container, EventModel $event)
    {
        parent::__construct($container, 'fyziklani.submit-form');
        $this->event = $event;
    }

    /**
     * @throws NotSetGameParametersException
     */
    protected function getData(): array
    {
        return [
            'availablePoints' => $this->event->getFyziklaniGameSetup()->getAvailablePoints(),
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
            $handler = new Handler($this->event, $this->getContext());
            $handler->preProcess($this->getLogger(), $data['code'], +$data['points']);
        } catch (TaskCodeException | ClosedSubmittingException $exception) {
            $this->getLogger()->log(new Message($exception->getMessage(), Message::LVL_ERROR));
        }
        $this->sendAjaxResponse();
    }
}
