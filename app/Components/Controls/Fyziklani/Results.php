<?php

namespace  FKSDB\Components\Controls\Fyziklani;

use FKS\Application\IJavaScriptCollector;
use Nette\Utils\Json;

class Results extends ReactComponent {
    /**
     * @var array
     */
    private $otherParams;
    /**
     * @var array
     */
    private $rooms;
    /**
     * @var array
     */
    private $tasks;
    /**
     * @var array
     */
    private $teams;

    /**
     * @var bool
     */
    private static $JSAttached = false;

    public function setParams(array $otherParams) {
        return $this->otherParams = $otherParams;
    }

    public function setTeams(array $teams) {
        return $this->teams = $teams;
    }

    public function setTasks(array $tasks) {
        return $this->tasks = $tasks;
    }

    public function setRooms(array $rooms) {
        return $this->rooms = $rooms;
    }

    public function render() {
        $this->template->teams = Json::encode($this->teams);
        $this->template->tasks = Json::encode($this->tasks);
        $this->template->rooms = Json::encode($this->rooms);
        $this->template->params = Json::encode($this->otherParams);

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'Results.latte');
        $this->template->render();
    }

    protected function attached($obj) {
        parent::attached($obj);
        if (!static::$JSAttached && $obj instanceof IJavaScriptCollector) {
            static::$JSAttached = true;
            $obj->registerJSFile('js/tablesorter.min.js');
            $obj->registerJSFile('js/bundle-results.min.js');
        }
    }
}
