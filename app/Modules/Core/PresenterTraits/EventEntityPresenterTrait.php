<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\EventModel;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Fykosak\NetteORM\Model\Model;
use Nette\Application\ForbiddenRequestException;

/**
 * @phpstan-template TEventModel of (Model&\Nette\Security\Resource)
 */
trait EventEntityPresenterTrait
{
    /** @phpstan-use EntityPresenterTrait<TEventModel> */
    use EntityPresenterTrait {
        getEntity as getBaseEntity;
    }

    /**
     * @throws CannotAccessModelException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws GoneException
     * @throws \ReflectionException
     * @phpstan-return TEventModel
     */
    protected function getEntity(): Model
    {
        /** @phpstan-var TEventModel $model */
        $model = $this->getBaseEntity();
        /** @var EventModel $event */
        $event = $model->getReferencedModel(EventModel::class);
        if ($event->event_id !== $this->getEvent()->event_id) {
            throw new ForbiddenRequestException();
        }
        return $model;
    }

    /**
     * @throws EventNotFoundException
     */
    abstract protected function getEvent(): EventModel;
}
