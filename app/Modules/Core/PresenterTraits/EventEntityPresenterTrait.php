<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\GoneException;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\ReferencedAccessor;
use Fykosak\NetteORM\Model;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Nette\Application\ForbiddenRequestException;

trait EventEntityPresenterTrait
{
    use EntityPresenterTrait {
        getEntity as getBaseEntity;
    }

    /**
     * @throws CannotAccessModelException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     * @throws GoneException
     */
    protected function getEntity(): Model
    {
        $model = $this->getBaseEntity();
        /** @var ModelEvent $event */
        $event = ReferencedAccessor::accessModel($model, ModelEvent::class);
        if ($event->event_id !== $this->getEvent()->event_id) {
            throw new ForbiddenRequestException();
        }
        return $model;
    }

    /**
     * @throws EventNotFoundException
     */
    abstract protected function getEvent(): ModelEvent;
}
