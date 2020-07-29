<?php

namespace FKSDB\Components\Controls\Fyziklani\Submit;

use FKSDB\Components\React\AjaxComponent;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\Fyziklani\Submit\ClosedSubmittingException;
use FKSDB\Fyziklani\Submit\HandlerFactory;
use FKSDB\Logging\MemoryLogger;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Application\IJavaScriptCollector;
use FKSDB\Messages\Message;
use FKSDB\Fyziklani\NotSetGameParametersException;
use FKSDB\Fyziklani\Submit\TaskCodeException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\React\ReactResponse;
use Nette\Application\AbortException;
use Nette\Application\UI\InvalidLinkException;
use Nette\DI\Container;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * Class TaskCodeInput
 * @author Michal Červeňák <miso@fykos.cz>
 */
class TaskCodeInput extends AjaxComponent {

    /** @var ServiceFyziklaniTeam */
    private $serviceFyziklaniTeam;

    /** @var ServiceFyziklaniTask */
    private $serviceFyziklaniTask;

    /** @var HandlerFactory */
    private $handlerFactory;

    /** @var ModelEvent */
    private $event;

    /**
     * TaskCodeInput constructor.
     * @param Container $container
     * @param ModelEvent $event
     */
    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container, 'fyziklani.submit-form');
        $this->event = $event;
        $this->monitor(IJavaScriptCollector::class, function (IJavaScriptCollector $collector) {
            $collector->registerJSFile('https://dmla.github.io/jsqrcode/src/qr_packed.js');
        });
    }

    final protected function getEvent(): ModelEvent {
        return $this->event;
    }

    /**
     * @param HandlerFactory $handlerFactory
     * @param ServiceFyziklaniTask $serviceFyziklaniTask
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @return void
     */
    public function injectPrimary(HandlerFactory $handlerFactory, ServiceFyziklaniTask $serviceFyziklaniTask, ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->handlerFactory = $handlerFactory;
    }

    /**
     * @param mixed ...$args
     * @return string
     * @throws JsonException
     * @throws NotSetGameParametersException
     */
    protected function getData(...$args): string {
        return Json::encode([
            'availablePoints' => $this->getEvent()->getFyziklaniGameSetup()->getAvailablePoints(),
            'tasks' => $this->serviceFyziklaniTask->getTasksAsArray($this->getEvent()),
            'teams' => $this->serviceFyziklaniTeam->getTeamsAsArray($this->getEvent()),
        ]);
    }

    /**
     * @return array
     * @throws InvalidLinkException
     */
    protected function getActions(): array {
        return [
            'save' => $this->link('save!'),
        ];
    }

    /**
     * @return void
     * @throws AbortException
     * @throws BadTypeException
     */
    public function handleSave() {
        $request = $this->getHttpRequest();
        $response = new ReactResponse();
        $response->setAct($request['act']);
        try {
            $handler = $this->handlerFactory->create($this->getEvent());
            $logger = new MemoryLogger();
            $handler->preProcess($logger, $request->requestData['code'], +$request->requestData['points']);
            $response->setMessages($logger->getMessages());
        } catch (TaskCodeException $exception) {
            $response->addMessage(new Message($exception->getMessage(), BasePresenter::FLASH_ERROR));
        } catch (ClosedSubmittingException $exception) {
            $response->addMessage(new Message($exception->getMessage(), BasePresenter::FLASH_ERROR));
        }
        $this->getPresenter()->sendResponse($response);

    }
}
