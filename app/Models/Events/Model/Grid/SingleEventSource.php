<?php

namespace FKSDB\Models\Events\Model\Grid;

use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\ServicesMulti\AbstractServiceMulti;
use Fykosak\NetteORM\AbstractService;
use Fykosak\NetteORM\TypedTableSelection;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Holder;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\DI\Container;
use Nette\InvalidStateException;
use Nette\SmartObject;

/**
 * @method SingleEventSource order()
 * @method SingleEventSource limit()
 * @method int count()
 * @method SingleEventSource where(string $cond, ...$args)
 */
class SingleEventSource implements HolderSource {

    use SmartObject;

    private ModelEvent $event;
    private Container $container;
    private EventDispatchFactory $eventDispatchFactory;
    private Selection $primarySelection;
    private Holder $dummyHolder;
    /** @var ActiveRow[] */
    private ?array $primaryModels = null;
    /** @var ActiveRow[][] */
    private ?array $secondaryModels = null;
    /** @var Holder[] */
    private array $holders = [];

    /**
     * SingleEventSource constructor.
     * @throws NeonSchemaException
     * @throws ConfigurationNotFoundException
     */
    public function __construct(ModelEvent $event, Container $container, EventDispatchFactory $eventDispatchFactory) {
        $this->event = $event;
        $this->container = $container;
        $this->eventDispatchFactory = $eventDispatchFactory;
        $this->dummyHolder = $eventDispatchFactory->getDummyHolder($this->event);
        $this->primarySelection = $this->dummyHolder->getPrimaryHolder()
            ->getService()
            ->getTable()
            ->where($this->dummyHolder->getPrimaryHolder()->getEventIdColumn(), $this->event->getPrimary());
    }

    public function getEvent(): ModelEvent {
        return $this->event;
    }

    public function getDummyHolder(): Holder {
        return $this->dummyHolder;
    }

    private function loadData(): void {
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
        /** @var AbstractService|AbstractServiceMulti[]|BaseHolder[][] $group */
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
     * @throws NeonSchemaException
     * @throws ConfigurationNotFoundException
     */
    private function createHolders(): void {
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
            $holder = $this->eventDispatchFactory->getDummyHolder($this->event);
            $holder->setModel($primaryModel, $cache[$primaryPK] ?? []);
            $this->holders[$primaryPK] = $holder;
        }
    }

    /**
     * Method propagates selected calls to internal primary models selection.
     *
     * @staticvar array $delegated
     * @return SingleEventSource|int
     */
    public function __call(string $name, array $args) {
        static $delegated = [
            'where' => false,
            'order' => false,
            'limit' => false,
            'count' => true,
        ];
        $result = $this->primarySelection->{$name}(...$args);
        // $result = call_user_func_array([$this->primarySelection, $name], $args);
        $this->primaryModels = null;

        if ($delegated[$name]) {
            return $result;
        } else {
            return $this;
        }
    }

    /**
     * @return Holder[]
     * @throws NeonSchemaException
     */
    public function getHolders(): array {
        if (!isset($this->primaryModels)) {
            $this->loadData();
            $this->createHolders();
        }
        return $this->holders;
    }

    /**
     * @throws NeonSchemaException
     */
    public function getHolder(int $primaryKey): Holder {
        $primaryModel = $this->dummyHolder->getPrimaryHolder()->getService()->findByPrimary($primaryKey);

        $cache = [];
        foreach ($this->dummyHolder->getGroupedSecondaryHolders() as $key => $group) {
            $secondaryModel = $group['service']->findByPrimary($primaryModel->{$group['joinOn']});
            $cache[$key] = $cache[$key] ?? [];
            $cache[$key][] = $secondaryModel;
        }

        $holder = $this->eventDispatchFactory->getDummyHolder($this->event);
        $holder->setModel($primaryModel, $cache);
        return $holder;
    }
}
