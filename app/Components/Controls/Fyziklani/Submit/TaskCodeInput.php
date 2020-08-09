<?php

namespace FKSDB\Components\Controls\Fyziklani\Submit;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\Fyziklani\Submit\ClosedSubmittingException;
use FKSDB\Fyziklani\Submit\HandlerFactory;
use FKSDB\Logging\MemoryLogger;
use FKSDB\Modules\Core\BasePresenter;
use FKSDB\Application\IJavaScriptCollector;
use FKSDB\Components\Controls\Fyziklani\FyziklaniReactControl;
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
class TaskCodeInput extends FyziklaniReactControl {

    private ServiceFyziklaniTeam $serviceFyziklaniTeam;

    private ServiceFyziklaniTask $serviceFyziklaniTask;

    private HandlerFactory $handlerFactory;

    /**
     * TaskCodeInput constructor.
     * @param Container $container
     * @param ModelEvent $event
     */
    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container, $event, 'fyziklani.submit-form');
        $this->monitor(IJavaScriptCollector::class, function (IJavaScriptCollector $collector) {
            $collector->registerJSFile('https://dmla.github.io/jsqrcode/src/qr_packed.js');
        });
    }

    public function injectPrimary(HandlerFactory $handlerFactory, ServiceFyziklaniTask $serviceFyziklaniTask, ServiceFyziklaniTeam $serviceFyziklaniTeam): void {
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
    public function getData(...$args): string {
        return Json::encode([
            'availablePoints' => $this->getEvent()->getFyziklaniGameSetup()->getAvailablePoints(),
            'tasks' => $this->serviceFyziklaniTask->getTasksAsArray($this->getEvent()),
            'teams' => $this->serviceFyziklaniTeam->getTeamsAsArray($this->getEvent()),
        ]);
    }

    /**
     * @return void
     * @throws InvalidLinkException
     */
    protected function configure(): void {
        $this->addAction('save', $this->link('save!'));
        parent::configure();
    }

    /**
     * @return void
     * @throws AbortException
     * @throws BadTypeException
     */
    public function handleSave(): void {
        $request = $this->getReactRequest();
        $response = new ReactResponse();
        $response->setAct($request->act);
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
