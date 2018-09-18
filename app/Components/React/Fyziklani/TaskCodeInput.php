<?php

namespace FKSDB\Components\React\Fyziklani;

use Nette\Utils\Json;

class TaskCodeInput extends FyziklaniModule {
    /**
     * @var array
     */
    private $teams;
    /**
     * @var array
     */
    private $tasks;

    public function setTeams($teams) {
        return $this->teams = $teams;
    }

    public function setTasks($tasks) {
        return $this->tasks = $tasks;
    }

    public function getData() {
        return Json::encode([
            'tasks' => $this->tasks,
            'teams' => $this->teams,
        ]);
    }

    protected function getMode() {
        return null;
    }

    protected function getComponentName() {
        return 'submit-form';
    }
}
