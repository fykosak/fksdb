<?php

namespace FKSDB\Components\Controls\Fyziklani\Submit;

use BasePresenter;
use Exception;
use FKSDB\Components\Controls\Fyziklani\FyziklaniReactControl;
use FKSDB\Messages\Message;
use FKSDB\model\Fyziklani\ClosedSubmittingException;
use FKSDB\model\Fyziklani\SubmitHandler;
use FKSDB\model\Fyziklani\TaskCodeException;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniRoom;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;
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
     * @var SubmitHandler
     */
    private $handler;

    /**
     * TaskCodeInput constructor.
     * @param SubmitHandler $handler
     * @param Container $container
     * @param ModelEvent $event
     * @param ServiceFyziklaniRoom $serviceFyziklaniRoom
     * @param ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @param ServiceFyziklaniTask $serviceFyziklaniTask
     * @param ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     */
    public function __construct(SubmitHandler $handler, Container $container, ModelEvent $event, ServiceFyziklaniRoom $serviceFyziklaniRoom, ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition, ServiceFyziklaniTeam $serviceFyziklaniTeam, ServiceFyziklaniTask $serviceFyziklaniTask, ServiceFyziklaniSubmit $serviceFyziklaniSubmit) {
        parent::__construct($container, $event, $serviceFyziklaniRoom, $serviceFyziklaniTeamPosition, $serviceFyziklaniTeam, $serviceFyziklaniTask, $serviceFyziklaniSubmit);
        $this->handler = $handler;
    }

    /**
     * @return string
     * @throws JsonException
     */
    public function getData(): string {
        return Json::encode([
            'availablePoints' => $this->event->getFyziklaniGameSetup()->getAvailablePoints(),
            'tasks' => $this->serviceFyziklaniTask->getTasksAsArray($this->event),
            'teams' => $this->serviceFyziklaniTeam->getTeamsAsArray($this->event),
        ]);
    }

    /**
     * @return string
     */
    public function getMode(): string {
        return '';
    }

    /**
     * @return string
     */
    public function getComponentName(): string {
        return 'submit-form';
    }

    /**
     * @return array
     * @throws InvalidLinkException
     */
    public function getActions(): array {
        $actions = parent::getActions();
        $actions['save'] = $this->link('save!');
        return $actions;
    }

    /**
     * @throws AbortException
     * @throws Exception
     */
    public function handleSave() {
        $request = $this->getReactRequest();
        $response = new ReactResponse();
        $response->setAct($request->act);
        try {
            $log = $this->handler->preProcess($request->requestData['code'], +$request->requestData['points']);
            $response->addMessage($log);
        } catch (TaskCodeException $exception) {
            $response->addMessage(new Message($exception->getMessage(), BasePresenter::FLASH_ERROR));
        } catch (ClosedSubmittingException $exception) {
            $response->addMessage(new Message($exception->getMessage(), BasePresenter::FLASH_ERROR));
        }
        $this->getPresenter()->sendResponse($response);

    }
}
