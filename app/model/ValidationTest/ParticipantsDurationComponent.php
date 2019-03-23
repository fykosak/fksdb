<?php

/**
 * Class ParticipantsDurationComponent
 */
class ParticipantsDurationComponent extends \Nette\Application\UI\Control {
    /**
     * @var array
     */
    private $logs = [];

    public function render() {
        $this->template->logs = $this->logs;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ParticipantsDurationComponent.latte');
        $this->template->render();
    }

    /**
     * @param $log
     */
    public function addLog($log) {
        $this->logs[] = $log;
    }
}
