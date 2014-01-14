<?php

namespace FKSDB\Components\Events;

use Events\Machine\Machine;
use Events\Model\Grid\IHolderSource;
use Events\Model\Holder;
use ModelEvent;
use Nette\Application\UI\Control;
use Nette\Utils\Strings;
use SystemContainer;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 */
class ApplicationsGrid extends Control {

    const NAME_PREFIX = 'application_';

    /**
     *
     * @var SystemContainer
     */
    private $container;

    /**
     * @var IHolderSource
     */
    private $source;

    /**
     * @var Holder[]
     */
    private $holders;

    /**
     * @var Machine[]
     */
    private $machines;

    /**
     * @var ModelEvent[]
     */
    private $eventApplications;

    function __construct(SystemContainer $container, IHolderSource $source) {
        parent::__construct();
        $this->container = $container;
        $this->source = $source;
    }

    private function processSource() {
        $this->eventApplications = array();
        foreach ($this->source as $key => $holder) {
            $this->eventApplications[$key] = $holder->getEvent();
            $this->holders[$key] = $holder;
            $this->machines[$key] = $this->container->createEventMachine($holder->getEvent());
        }
    }

    protected function createComponent($name) {
        $key = null;
        if (Strings::startsWith($name, self::NAME_PREFIX)) {
            $key = substr($name, strlen(self::NAME_PREFIX));
        }
        if (!$key) {
            parent::createComponent($name);
        }

        $component = new ApplicationComponent($this->machines[$key], $this->holders[$key]);
        return $component;
    }

    public function render() {
        $this->processSource();
        
        $this->template->eventApplications = $this->eventApplications;

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'ApplicationsGrid.latte');
        $this->template->render();
    }

}
