<?php

declare(strict_types=1);

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\ReferencedAccessor;
use Fykosak\NetteORM\AbstractModel;
use Fykosak\NetteORM\Exceptions\CannotAccessModelException;
use Nette\Application\ForbiddenRequestException;

trait EventEntityPresenterTrait
{
    use EntityPresenterTrait {
        getEntity as getBaseEntity;
    }

    /**
     * @return AbstractModel|null
     * @throws CannotAccessModelException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    protected function getEntity(): AbstractModel
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
