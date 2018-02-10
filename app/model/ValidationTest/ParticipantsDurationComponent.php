<?php
/**
 * Created by IntelliJ IDEA.
 * User: miso
 * Date: 9.2.2018
 * Time: 1:29
 */

class ParticipantsDurationComponent extends \Nette\Application\UI\Control {
    /**
     * @var array
     */
    private $log = [];

    public function render() {
        $this->template->log = $this->log;
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ParticipantsDurationComponent.latte');
        $this->template->render();
    }

    public function setLog(array $log) {
        $this->log = $log;
    }
}
