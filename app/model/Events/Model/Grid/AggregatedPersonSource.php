<?php

namespace Events\Model\Grid;

use ArrayIterator;
use Events\Model\Holder\Holder;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Tables\TypedTableSelection;
use Nette\DI\Container;
use Nette\SmartObject;

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
abstract class AggregatedPersonSource implements IHolderSource {
    use SmartObject;
    /**
     * @var TypedTableSelection
     */
    private $events;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Holder[]
     */
    private $holders = null;

    /**
     * AggregatedPersonSource constructor.
     * @param TypedTableSelection $events
     * @param Container $container
     */
    function __construct(TypedTableSelection $events, Container $container) {
        $this->events = $events;
        $this->container = $container;
    }

    private function loadData() {
        $this->holders = [];
        foreach ($this->events as $eventKey => $row) {
            $event = ModelEvent::createFromActiveRow($row);
            $result = $this->processEvent($event);

            if ($result instanceof SingleEventSource) {
                foreach ($result->getHolders() as $holderKey => $holder) {
                    $key = $eventKey . '_' . $holderKey;
                    $this->holders[$key] = $holder;
                }
            } elseif ($result instanceof Holder) {
                $key = $eventKey . '_';
                $this->holders[$key] = $result;
            }
        }
    }

    /**
     * @param ModelEvent $event
     * @return mixed
     */
    abstract function processEvent(ModelEvent $event);

    /**
     * Method propagates selected calls to internal primary models selection.
     *
     * @staticvar array $delegated
     * @param string $name
     * @param array $args
     * @return self
     */
    public function __call($name, $args) {
        static $delegated = [
            'where' => false,
            'order' => false,
            'limit' => false,
            'count' => true,
        ];
        $result = call_user_func_array([$this->events, $name], $args);
        $this->holders = null;

        if ($delegated[$name]) {
            return $result;
        } else {
            return $this;
        }
    }

    /**
     * @return ArrayIterator|\Traversable
     */
    public final function getIterator() {
        if ($this->holders === null) {
            $this->loadData();
        }
        return new ArrayIterator($this->holders);
    }

}
