<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\ModelsMulti\Events;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\EventParticipantModel;
use FKSDB\Models\ORM\Models\Events\ModelDsefParticipant;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\NetteORM\Model;
use Nette\Database\Table\Selection;

/**
 * @deprecated
 */
class ModelMDsefParticipant extends Model
{
    public EventParticipantModel $mainModel;
    public ModelDsefParticipant $joinedModel;

    public function __construct(EventParticipantModel $mainModel, ModelDsefParticipant $joinedModel)
    {
        parent::__construct($joinedModel->toArray(), $joinedModel->getTable());
        $this->joinedModel = $joinedModel;
        $this->mainModel = $mainModel;
    }

    public function __toString(): string
    {
        return $this->mainModel->person->getFullName();
    }

    public function getEvent(): EventModel
    {
        return $this->mainModel->event;
    }

    public function getPerson(): PersonModel
    {
        return $this->mainModel->person;
    }


    public function toArray(): array
    {
        return $this->mainModel->toArray() + parent::toArray();
    }

    /**
     * @return bool|mixed|Selection|null
     * @throws \ReflectionException
     */
    public function &__get(string $key)
    {
        if ($this->mainModel->__isset($key)) {
            return $this->mainModel->__get($key);
        }
        if (parent::__isset($key)) {
            return parent::__get($key);
        }
        // this reference isn't that important
        $null = null;
        return $null;
    }

    /**
     * @param string|int $key
     */
    public function __isset($key): bool
    {
        return $this->mainModel->__isset($key) || parent::__isset($key);
    }

    /**
     * @param mixed $column
     */
    public function offsetExists($column): bool
    {
        return $this->__isset($column);
    }

    /**
     * @param mixed $column
     * @return bool|mixed|Selection|null
     * @throws \ReflectionException
     */
    public function &offsetGet($column)
    {
        return $this->__get($column);
    }
}
