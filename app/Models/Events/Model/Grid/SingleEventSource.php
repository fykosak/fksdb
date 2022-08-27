<?php

declare(strict_types=1);

namespace FKSDB\Models\Events\Model\Grid;

use FKSDB\Models\Events\Exceptions\ConfigurationNotFoundException;
use FKSDB\Models\Expressions\NeonSchemaException;
use FKSDB\Models\Events\EventDispatchFactory;
use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\ServicesMulti\ServiceMulti;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\Service;
use Fykosak\NetteORM\TypedSelection;
use FKSDB\Models\Events\Model\Holder\BaseHolder;
use FKSDB\Models\Events\Model\Holder\Holder;
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
class SingleEventSource implements HolderSource
{
    use SmartObject;

    private EventModel $event;
    private Container $container;
    private EventDispatchFactory $eventDispatchFactory;
    private Selection $primarySelection;
    private Holder $dummyHolder;
    /** @var Model[] */
    private ?array $primaryModels = null;
    /** @var Model[][] */
    private ?array $secondaryModels = null;
    /** @var Holder[] */
    private array $holders = [];

    /**
     * SingleEventSource constructor.
     * @throws NeonSchemaException
     * @throws ConfigurationNotFoundException
     */
    public function __construct(EventModel $event, Container $container, EventDispatchFactory $eventDispatchFactory)
    {
        $this->event = $event;
        $this->container = $container;
        $this->eventDispatchFactory = $eventDispatchFactory;
        $this->dummyHolder = $eventDispatchFactory->getDummyHolder($this->event);
        $this->primarySelection = $this->dummyHolder->primaryHolder
            ->getService()
            ->getTable()
            ->where($this->dummyHolder->primaryHolder->eventIdColumn, $this->event->getPrimary());
    }

    public function getEvent(): EventModel
    {
        return $this->event;
    }

    public function getDummyHolder(): Holder
    {
        return $this->dummyHolder;
    }

    private function loadData(): void
    {
        $joinToCheck = null;
        // load primaries
        $joinTo = $joinToCheck ?: $this->primarySelection->getPrimary();
        $this->primaryModels = $this->primarySelection->fetchPairs($joinTo);

        // invalidate holders
        $this->holders = [];
    }

    /**
     * @throws NeonSchemaException
     * @throws ConfigurationNotFoundException
     */
    private function createHolders(): void
    {
        foreach ($this->primaryModels as $primaryPK => $primaryModel) {
            $holder = $this->eventDispatchFactory->getDummyHolder($this->event);
            $holder->setModel($primaryModel);
            $this->holders[$primaryPK] = $holder;
        }
    }

    /**
     * Method propagates selected calls to internal primary models selection.
     *
     * @staticvar array $delegated
     * @return SingleEventSource|int
     */
    public function __call(string $name, array $args)
    {
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
    public function getHolders(): array
    {
        if (!isset($this->primaryModels)) {
            $this->loadData();
            $this->createHolders();
        }
        return $this->holders;
    }

    /**
     * @throws NeonSchemaException
     */
    public function getHolder(Model $primaryModel): Holder
    {
        $holder = $this->eventDispatchFactory->getDummyHolder($this->event);
        $holder->setModel($primaryModel);
        return $holder;
    }
}
