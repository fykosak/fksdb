<?php

namespace FKSDB\Events\Model\Grid;

use FKSDB\Config\NeonSchemaException;
use FKSDB\Events\EventDispatchFactory;
use FKSDB\ORM\IModel;
use FKSDB\ORM\IService;
use FKSDB\ORM\Models\ModelEvent;
use FKSDB\ORM\Tables\TypedTableSelection;
use FKSDB\Events\Model\Holder\BaseHolder;
use FKSDB\Events\Model\Holder\Holder;
use Nette\Application\BadRequestException;
use Nette\DI\Container;
use Nette\InvalidStateException;
use Nette\SmartObject;

/**
 * Due to author's laziness there's no class doc (or it's self explaining).
 *
 * @author Michal Koutný <michal@fykos.cz>
 *
 * @method SingleEventSource order()
 * @method SingleEventSource limit()
 * @method SingleEventSource count()
 * @method SingleEventSource where(string $cond, ...$args)
 */
class SingleEventSource implements IHolderSource {
    use SmartObject;

    /**
     * @var ModelEvent
     */
    private $event;

    /**
     * @var Container
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
     * @var TypedTableSelection
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
    private $holders = [];

    /**
     * SingleEventSource constructor.
     * @param ModelEvent $event
     * @param Container $container
     * @throws NeonSchemaException
     * @throws BadRequestException
     */
    public function __construct(ModelEvent $event, Container $container) {
        $this->event = $event;
        $this->container = $container;
        /** @var EventDispatchFactory $factory */
        $factory = $this->container->getByType(EventDispatchFactory::class);
        $this->dummyHolder = $factory->getDummyHolder($this->event);

        $primaryHolder = $this->dummyHolder->getPrimaryHolder();
        $eventIdColumn = $primaryHolder->getEventId();
        $this->primarySelection = $primaryHolder->getService()->getTable()->where($eventIdColumn, $this->event->getPrimary());
    }

    /**
     * @return ModelEvent
     */
    public function getEvent() {
        return $this->event;
    }

    /**
     * @return Holder
     */
    public function getDummyHolder() {
        return $this->dummyHolder;
    }

    private function loadData() {
        $joinToCheck = false;
        foreach ($this->dummyHolder->getGroupedSecondaryHolders() as $key => $group) {
            if ($joinToCheck === false) {
                $joinToCheck = $group['joinTo'];
            } elseif ($group['joinTo'] !== $joinToCheck) {
                throw new InvalidStateException(sprintf("SingleEventSource needs all secondary holders to be joined to the same column. Conflict '%s' and '%s'.", $group['joinTo'], $joinToCheck));
            }
        }
        // load primaries
        $joinTo = $joinToCheck ?: $this->primarySelection->getPrimary();
        $this->primaryModels = $this->primarySelection->fetchPairs($joinTo);

        $joinValues = array_keys($this->primaryModels);

        // load secondaries
        /** @var IService[]|BaseHolder[] $group */
        foreach ($this->dummyHolder->getGroupedSecondaryHolders() as $key => $group) {
            /** @var TypedTableSelection $secondarySelection */
            $secondarySelection = $group['service']->getTable()->where($group['joinOn'], $joinValues);
            if ($joinToCheck) {
                /** @var ModelEvent $event */
                $event = reset($group['holders'])->getEvent();
                $secondarySelection->where(BaseHolder::EVENT_COLUMN, $event->getPrimary());
            }

            $secondaryPK = $secondarySelection->getPrimary();
            if (!isset($this->secondaryModels[$key])) {
                $this->secondaryModels[$key] = [];
            }
            $this->secondaryModels[$key] = $secondarySelection->fetchPairs($secondaryPK);
        }

        // invalidate holders
        $this->holders = [];
    }

    /**
     * @return void
     * @throws BadRequestException
     * @throws NeonSchemaException
     */
    private function createHolders() {
        $cache = [];
        foreach ($this->dummyHolder->getGroupedSecondaryHolders() as $key => $group) {
            foreach ($this->secondaryModels[$key] as $secondaryPK => $secondaryModel) {
                $primaryPK = $secondaryModel[$group['joinOn']];
                if (!isset($cache[$primaryPK])) {
                    $cache[$primaryPK] = [];
                }
                if (!isset($cache[$primaryPK][$key])) {
                    $cache[$primaryPK][$key] = [];
                }
                $cache[$primaryPK][$key][] = $secondaryModel;
            }
        }
        foreach ($this->primaryModels as $primaryPK => $primaryModel) {
            /** @var EventDispatchFactory $factory */
            $factory = $this->container->getByType(EventDispatchFactory::class);
            $holder = $factory->getDummyHolder($this->event);
            $holder->setModel($primaryModel, isset($cache[$primaryPK]) ? $cache[$primaryPK] : []);
            $this->holders[$primaryPK] = $holder;
        }
    }

    /**
     * Method propagates selected calls to internal primary models selection.
     *
     * @staticvar array $delegated
     * @param string $name
     * @param array $args
     * @return SingleEventSource
     */
    public function __call($name, $args) {
        static $delegated = [
            'where' => false,
            'order' => false,
            'limit' => false,
            'count' => true,
        ];
        $result = call_user_func_array([$this->primarySelection, $name], $args);
        $this->primaryModels = null;

        if ($delegated[$name]) {
            return $result;
        } else {
            return $this;
        }
    }

    /**
     * @return Holder[]
     * @throws BadRequestException
     * @throws NeonSchemaException
     */
    public function getHolders(): array {
        if ($this->primaryModels === null) {
            $this->loadData();
            $this->createHolders();
        }
        return $this->holders;
    }
}
