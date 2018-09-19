<?php

namespace Events\Model\Grid;

use ArrayIterator;
use Events\Model\Holder\Holder;
use ModelEvent;
use Nette\Object;
use ORM\Tables\TypedTableSelection;
use SystemContainer;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 *
 * @method SingleEventSource where()
 * @method SingleEventSource order()
 * @method SingleEventSource limit()
 * @method SingleEventSource count()
 */
abstract class AggregatedPersonSource extends Object implements IHolderSource {

    /**
     * @var TypedTableSelection
     */
    private $events;

    /**
     * @var SystemContainer
     */
    protected $container;

    /**
     *
     * @var Holder[]
     */
    private $holders = null;

    function __construct(TypedTableSelection $events, SystemContainer $container) {
        $this->events = $events;
        $this->container = $container;
    }

    private function loadData() {
        $this->holders = array();
        foreach ($this->events as $eventKey => $event) {
            $result = $this->processEvent($event);

            if ($result instanceof SingleEventSource) {
                foreach ($result as $holderKey => $holder) {
                    $key = $eventKey . '_' . $holderKey;
                    $this->holders[$key] = $holder;
                }
            } else if ($result instanceof Holder) {
                $key = $eventKey . '_';
                $this->holders[$key] = $result;
            }
        }
    }

    abstract function processEvent(ModelEvent $event);

    /**
     * Method propagates selected calls to internal primary models selection.
     *
     * @staticvar array $delegated
     * @param string $name
     * @param array $args
     * @return \Events\Model\Grid\SingleEventSource
     */
    public function __call($name, $args) {
        static $delegated = array(
            'where' => false,
            'order' => false,
            'limit' => false,
            'count' => true,
        );
        if (!isset($delegated[$name])) {
            return parent::__call($name, $args);
        }
        $result = call_user_func_array(array($this->events, $name), $args);
        $this->holders = null;

        if ($delegated[$name]) {
            return $result;
        } else {
            return $this;
        }
    }

    public final function getIterator() {
        if ($this->holders === null) {
            $this->loadData();
        }
        return new ArrayIterator($this->holders);
    }

}
