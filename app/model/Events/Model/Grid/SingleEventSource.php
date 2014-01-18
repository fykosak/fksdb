<?php

namespace Events\Model\Grid;

use ArrayIterator;
use Events\Model\Holder\Holder;
use ModelEvent;
use Nette\Database\Table\Selection;
use Nette\Object;
use ORM\IModel;
use SystemContainer;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 * 
 * @author Michal Koutný <michal@fykos.cz>
 * 
 * @method SingleEventSource order()
 * @method SingleEventSource limit()
 * @method SingleEventSource count() 
 */
class SingleEventSource extends Object implements IHolderSource {

    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * @var SystemContainer
     */
    private $container;

    /**
     * @var IModel[]
     */
    private $primaryModels = null;

    /**
     *
     * @var IModel[][]
     */
    private $secondaryModels = null;

    /**
     * @var Selection
     */
    private $primarySelection;

    /**
     * @var Holder
     */
    private $dummyHolder;

    /**
     *
     * @var Holder[]
     */
    private $holders = array();

    function __construct(ModelEvent $event, SystemContainer $container) {
        $this->event = $event;
        $this->container = $container;

        $this->dummyHolder = $this->container->createEventHolder($this->event);

        $primaryHolder = $this->dummyHolder->getPrimaryHolder();
        $eventIdColumn = $primaryHolder->getEventId();
        $this->primarySelection = $primaryHolder->getService()->getTable()->where($eventIdColumn, $this->event->getPrimary());
    }

    public function getDummyHolder() {
        return $this->dummyHolder;
    }

    private function loadData() {
        // load primaries
        $primaryPK = $this->primarySelection->getPrimary();
        $this->primaryModels = $this->primarySelection->fetchPairs($primaryPK);

        $keys = array_keys($this->primaryModels);

        // load secondaries
        foreach ($this->dummyHolder->getGroupedSecondaryHolders() as $key => $group) {
            $secondarySelection = $group['service']->getTable()->where($group['joinOn'], $keys);
            $secondaryPK = $secondarySelection->getPrimary();
            if (!isset($this->secondaryModels[$key])) {
                $this->secondaryModels[$key] = array();
            }
            $this->secondaryModels[$key] = $secondarySelection->fetchPairs($secondaryPK);
        }

        // invalidate holders
        $this->holders = array();
    }

    private function createHolders() {
        $cache = array();
        foreach ($this->dummyHolder->getGroupedSecondaryHolders() as $key => $group) {
            foreach ($this->secondaryModels[$key] as $secondaryPK => $secondaryModel) {
                $primaryPK = $secondaryModel[$group['joinOn']];
                if (!isset($cache[$primaryPK])) {
                    $cache[$primaryPK] = array();
                }
                if (!isset($cache[$primaryPK][$key])) {
                    $cache[$primaryPK][$key] = array();
                }
                $cache[$primaryPK][$key][] = $secondaryModel;
            }
        }
        foreach ($this->primaryModels as $primaryPK => $primaryModel) {
            $holder = $this->container->createEventHolder($this->event);
            $holder->setModel($primaryModel, isset($cache[$primaryPK]) ? $cache[$primaryPK] : array());
            $this->holders[$primaryPK] = $holder;
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
        $result = call_user_func_array(array($this->primarySelection, $name), $args);
        $this->primaryModels = null;

        if ($delegated[$name]) {
            return $result;
        } else {
            return $this;
        }
    }

    public function getIterator() {
        if ($this->primaryModels === null) {
            $this->loadData();
            $this->createHolders();
        }
        return new ArrayIterator($this->holders);
    }

}
