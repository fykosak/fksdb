<?php

namespace FKSDB\Components\Controls\Fyziklani;

use FKSDB\model\Fyziklani\ClosedSubmittingException;
use FKSDB\model\Fyziklani\TaskCodeException;
use FKSDB\model\Fyziklani\TaskCodeHandler;
use FKSDB\ORM\ModelEvent;
use Nette\DI\Container;
use Nette\Utils\Json;
use ORM\Services\Events\ServiceFyziklaniTeam;

/**
 * Class TaskCodeInput
 * @package FKSDB\Components\Controls\Fyziklani
 */
class TaskCodeInput extends FyziklaniReactControl {
    /**
     * @var TaskCodeHandler
     */
    private $handler;

    /**
     * TaskCodeInput constructor.
     * @param TaskCodeHandler $handler
     * @param Container $container
     * @param ModelEvent $event
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniRoom $serviceFyziklaniRoom
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition
     * @param ServiceFyziklaniTeam $serviceFyziklaniTeam
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask $serviceFyziklaniTask
     * @param \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit $serviceFyziklaniSubmit
     */
    public function __construct(TaskCodeHandler $handler, Container $container, ModelEvent $event, \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniRoom $serviceFyziklaniRoom, \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition, ServiceFyziklaniTeam $serviceFyziklaniTeam, \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTask $serviceFyziklaniTask, \FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniSubmit $serviceFyziklaniSubmit) {
        parent::__construct($container, $event, $serviceFyziklaniRoom, $serviceFyziklaniTeamPosition, $serviceFyziklaniTeam, $serviceFyziklaniTask, $serviceFyziklaniSubmit);
        $this->handler = $handler;
    }

    /**
     * @return string
     * @throws \Nette\Utils\JsonException
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
     * @throws \Nette\Application\UI\InvalidLinkException
     */
    public function getActions(): array {
        $actions = parent::getActions();
        $actions['save'] = $this->link('save!');
        return $actions;
    }

    /**
     * @throws \Nette\Application\AbortException
     */
    public function handleSave() {
        $request = $this->getReactRequest();
        $response = new \ReactResponse();
        $response->setAct($request->act);
        try {
            $log = $this->handler->preProcess($request->requestData['code'], +$request->requestData['points']);
            $response->addMessage(new \ReactMessage($log, \BasePresenter::FLASH_SUCCESS));
        } catch (TaskCodeException $e) {
            $response->addMessage(new \ReactMessage($e->getMessage(), \BasePresenter::FLASH_ERROR));
        } catch (ClosedSubmittingException $e) {
            $response->addMessage(new \ReactMessage($e->getMessage(), \BasePresenter::FLASH_ERROR));
        }
        $this->getPresenter()->sendResponse($response);

    }
}
