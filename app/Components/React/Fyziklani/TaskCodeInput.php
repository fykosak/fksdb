<?php

namespace FKSDB\Components\React\Fyziklani;

use FKSDB\ORM\ModelEvent;
use Nette\Utils\Json;
use ORM\Services\Events\ServiceFyziklaniTeam;
use ServiceFyziklaniTask;

class TaskCodeInput extends FyziklaniModule {
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;
    /**
     * @var ServiceFyziklaniTask
     */
    private $serviceFyziklaniTask;
    /**
     * @var ModelEvent
     */
    private $event;

    public function __construct(\ServiceBrawlRoom $serviceBrawlRoom, ServiceFyziklaniTeam $serviceFyziklaniTeam, ServiceFyziklaniTask $serviceFyziklaniTask, ModelEvent $event) {
        parent::__construct($serviceBrawlRoom, $event);
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->event = $event;
    }

    public function getData(): string {
        return Json::encode([
            'tasks' => $this->serviceFyziklaniTask->getTasks($this->event->event_id),
            'teams' => $this->serviceFyziklaniTeam->getTeams($this->event->event_id),
        ]);
    }

    public function getMode(): string {
        return null;
    }

    public function getComponentName(): string {
        return 'submit-form';
    }
}
