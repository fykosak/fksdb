<?php

declare(strict_types=1);

namespace FKSDB\Components\Controls\Fyziklani\Submit;

use FKSDB\Components\Controls\Loaders\JavaScriptCollector;
use FKSDB\Models\Fyziklani\Submit\ClosedSubmittingException;
use FKSDB\Models\Fyziklani\Submit\HandlerFactory;
use Fykosak\NetteFrontendComponent\Components\AjaxComponent;
use Fykosak\Utils\Logging\Message;
use FKSDB\Models\Fyziklani\NotSetGameParametersException;
use FKSDB\Models\Fyziklani\Submit\TaskCodeException;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;

class PointsEntryComponent extends AjaxComponent
{
    private ServiceFyziklaniTeam $serviceFyziklaniTeam;
    private ServiceFyziklaniTask $serviceFyziklaniTask;
    private HandlerFactory $handlerFactory;
    private ModelEvent $event;

    public function __construct(Container $container, ModelEvent $event)
    {
        parent::__construct($container, 'fyziklani.submit-form');
        $this->event = $event;
        $this->monitor(JavaScriptCollector::class, function (JavaScriptCollector $collector) {
            $collector->registerJSFile('https://dmla.github.io/jsqrcode/src/qr_packed.js');
        });
    }

    final public function injectPrimary(
        HandlerFactory $handlerFactory,
        ServiceFyziklaniTask $serviceFyziklaniTask,
        ServiceFyziklaniTeam $serviceFyziklaniTeam
    ): void {
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * @throws NotSetGameParametersException
     */
    protected function getData(): array
    {
        return [
            'availablePoints' => $this->event->getFyziklaniGameSetup()->getAvailablePoints(),
            'tasks' => $this->serviceFyziklaniTask->serialiseTasks($this->event),
            'teams' => $this->serviceFyziklaniTeam->serialiseTeams($this->event),
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
            $handler = $this->handlerFactory->create($this->event);
            $handler->preProcess($this->getLogger(), $data['code'], +$data['points']);
        } catch (TaskCodeException | ClosedSubmittingException $exception) {
            $this->getLogger()->log(new Message($exception->getMessage(), Message::LVL_ERROR));
        }
        $this->sendAjaxResponse();
    }
}
