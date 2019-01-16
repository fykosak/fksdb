<?php

namespace FKSDB\Components\React\Fyziklani;

use FKSDB\model\Fyziklani\TaskCodeException;
use FKSDB\model\Fyziklani\TaskCodeHandler;
use FKSDB\ORM\ModelEvent;
use Nette\DI\Container;
use Nette\Utils\Json;
use ORM\Services\Events\ServiceFyziklaniTeam;

class TaskCodeInput extends FyziklaniModule {
    /**
     * @var TaskCodeHandler
     */
    private $handler;

    public function __construct(TaskCodeHandler $handler, Container $container, ModelEvent $event, \ServiceFyziklaniRoom $serviceFyziklaniRoom, \ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition, ServiceFyziklaniTeam $serviceFyziklaniTeam, \ServiceFyziklaniTask $serviceFyziklaniTask, \ServiceFyziklaniSubmit $serviceFyziklaniSubmit) {
        parent::__construct($container, $event, $serviceFyziklaniRoom, $serviceFyziklaniTeamPosition, $serviceFyziklaniTeam, $serviceFyziklaniTask, $serviceFyziklaniSubmit);
        $this->handler = $handler;
    }

    public function getData(): string {
        return Json::encode([
            'availablePoints' => $this->event->getFyziklaniGameSetup()->getAvailablePoints(),
            'tasks' => $this->serviceFyziklaniTask->getTasksAsArray($this->event),
            'teams' => $this->serviceFyziklaniTeam->getTeamsAsArray($this->event),
        ]);
    }

    public function getMode(): string {
        return '';
    }

    public function getComponentName(): string {
        return 'submit-form';
    }

    protected function getActions() {
        $actions = parent::getActions();
        $actions['save'] = $this->link('save!');
        return $actions;
    }

    public function handleSave() {
        $request = $this->getReactRequest();
        $response = new \ReactResponse();
        $response->setAct($request->act);
        try {
            $log = $this->handler->preProcess($request->requestData['code'], +$request->requestData['points']);
            $response->addMessage(new \ReactMessage($log, 'success'));
        } catch (TaskCodeException $e) {
            $response->addMessage(new \ReactMessage($e->getMessage(), 'danger'));
        }
        $this->getPresenter()->sendResponse($response);

    }
}
