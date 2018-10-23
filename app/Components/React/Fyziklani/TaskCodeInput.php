<?php

namespace FKSDB\Components\React\Fyziklani;

use Nette\Utils\Json;

class TaskCodeInput extends FyziklaniModule {

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
