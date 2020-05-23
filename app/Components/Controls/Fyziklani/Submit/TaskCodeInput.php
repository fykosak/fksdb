<?php

namespace FKSDB\Components\Controls\Fyziklani\Submit;

use BasePresenter;
use Exception;
use FKSDB\Application\IJavaScriptCollector;
use FKSDB\Components\Controls\Fyziklani\FyziklaniReactControl;
use FKSDB\Messages\Message;
use FKSDB\Fyziklani\ClosedSubmittingException;
use FKSDB\Fyziklani\NotSetGameParametersException;
use FKSDB\Fyziklani\SubmitHandler;
use FKSDB\Fyziklani\TaskCodeException;
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
 * @package FKSDB\Components\Controls\Fyziklani
 */
class TaskCodeInput extends FyziklaniReactControl {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;

    /**
     * @var ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;

    /**
     * TaskCodeInput constructor.
     * @param Container $container
     * @param ModelEvent $event
     */
    public function __construct(Container $container, ModelEvent $event) {
        parent::__construct($container, $event);
        $this->monitor(IJavaScriptCollector::class);
    }

    /**
     * @param ServiceFyziklaniTask $serviceFyziklaniTask
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @return void
     */
    public function injectPrimary(ServiceFyziklaniTask $serviceFyziklaniTask, ServiceFyziklaniTeam $serviceFyziklaniTeam) {
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
    }

    /**
     * @return string
     * @throws JsonException
     * @throws NotSetGameParametersException
     */
    public function getData(): string {
        return Json::encode([
            'availablePoints' => $this->getEvent()->getFyziklaniGameSetup()->getAvailablePoints(),
            'tasks' => $this->serviceFyziklaniTask->getTasksAsArray($this->getEvent()),
            'teams' => $this->serviceFyziklaniTeam->getTeamsAsArray($this->getEvent()),
        ]);
    }

    /**
     * @param $obj
     * @return void
     */
    protected function attached($obj) {
        if ($obj instanceof IJavaScriptCollector) {
            $obj->registerJSFile('https://dmla.github.io/jsqrcode/src/qr_packed.js');
        }
        parent::attached($obj);
    }

    /**
     * @return void
     * @throws InvalidLinkException
     */
    protected function configure() {
        $this->addAction('save', $this->link('save!'));
        parent::configure();
    }

    /**
     * @return void
     * @throws Exception
     * @throws AbortException
     */
    public function handleSave() {
        $request = $this->getReactRequest();
        $response = new ReactResponse();
        $response->setAct($request->act);
        try {
            $handler = new SubmitHandler($this->getContext(), $this->getEvent());
            $log = $handler->preProcess($request->requestData['code'], +$request->requestData['points'], $this->getPresenter()->getUser());
            $response->addMessage($log);
        } catch (TaskCodeException $exception) {
            $response->addMessage(new Message($exception->getMessage(), BasePresenter::FLASH_ERROR));
        } catch (ClosedSubmittingException $exception) {
            $response->addMessage(new Message($exception->getMessage(), BasePresenter::FLASH_ERROR));
        }
        $this->getPresenter()->sendResponse($response);

    }

    protected function getReactId(): string {
        return 'fyziklani.submit-form';
    }
}
