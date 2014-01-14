<?php

namespace Events\Model\Grid;

use ArrayIterator;
use Events\Model\Holder;
use ModelPerson;
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
class RelatedPersonSource extends Object implements IHolderSource {

    /**
     * @var ModelPerson
     */
    private $person;

    /**
     * @var TypedTableSelection
     */
    private $events;

    /**
     * @var SystemContainer
     */
    private $container;

    /**
     *
     * @var Holder[]
     */
    private $holders = null;

    function __construct(ModelPerson $person, TypedTableSelection $events, SystemContainer $container) {
        $this->person = $person;
        $this->events = $events;
        $this->container = $container;
    }

    private function loadData() {
        $this->holders = array();
        $personId = $this->person->getPrimary();

        foreach ($this->events as $eventKey => $event) {
            $eventSource = new SingleEventSource($event, $this->container);

            $subconditions = array();
            $count = 0;

            $primaryPersonIds = $eventSource->getDummyHolder()->getPrimaryHolder()->getPersonIds();
            $subconditions[] = implode(' = ?  OR ', $primaryPersonIds) . ' = ?';
            $count += count($primaryPersonIds);

            foreach ($eventSource->getDummyHolder()->getGroupedSecondaryHolders() as $group) {
                $subconditions[] = implode(' = ?  OR ', $group['personIds']) . ' = ?';
                $count += count($group['personIds']);
            }

            if ($count == 1) {
                $parameters = $personId;
            } else {
                $parameters = array_fill(0, $count, $personId);
            }
            $eventSource->where(implode(' OR ', $subconditions), $parameters);

            foreach ($eventSource as $holderKey => $holder) {
                $key = $eventKey . '_' . $holderKey;
                $this->holders[$key] = $holder;
            }
        }
    }

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

    public function getIterator() {
        if ($this->holders === null) {
            $this->loadData();
        }
        return new ArrayIterator($this->holders);
    }

}
