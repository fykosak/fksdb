<?php

namespace  FKSDB\Components\Controls\Fyziklani;

use FKS\Application\IJavaScriptCollector;
use Nette\Utils\Json;

class TaskCodeInput extends ReactComponent {
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

    /**
     * @throws \Nette\Utils\JsonException
     */
    public function render() {
        $this->template->tasks = Json::encode($this->tasks);
        $this->template->teams = Json::encode($this->teams);
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'TaskCodeInput.latte');
        $this->template->render();
    }
}
