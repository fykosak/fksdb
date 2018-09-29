<?php

namespace FKSDB\Components\React\Fyziklani;

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
     * @var integer
     */
    private $eventId;

    public function __construct(ServiceFyziklaniTeam $serviceFyziklaniTeam, ServiceFyziklaniTask $serviceFyziklaniTask, $eventId) {
        $this->serviceFyziklaniTask = $serviceFyziklaniTask;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->eventId = $eventId;
        parent::__construct();
    }

    public function getData(): string {
        return Json::encode([
            'tasks' => $this->serviceFyziklaniTask->getTasks($this->eventId),
            'teams' => $this->serviceFyziklaniTeam->getTeams($this->eventId),
        ]);
    }

    public function getMode(): string {
        return null;
    }

    public function getComponentName(): string {
        return 'submit-form';
    }
}
