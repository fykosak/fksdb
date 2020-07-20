<?php

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Entity\ModelNotFoundException;
use FKSDB\Events\EventNotFoundException;
use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\ForbiddenRequestException;

/**
 * Trait EventEntityTrait
 * @author Michal Červeňák <miso@fykos.cz>
 */
trait EventEntityPresenterTrait {
    use EntityPresenterTrait {
        getEntity as getBaseEntity;
    }

    /**
     * @return AbstractModelSingle|IEventReferencedModel|null
     * @throws BadTypeException
     * @throws EventNotFoundException
     * @throws ForbiddenRequestException
     * @throws ModelNotFoundException
     */
    protected function getEntity() {
        $model = $this->getBaseEntity();

        if (!$model instanceof IEventReferencedModel) {
            throw new BadTypeException(IEventReferencedModel::class, $model);
        }
        if ($model->getEvent()->event_id !== $this->getEvent()->event_id) {
            throw new ForbiddenRequestException();
        }
        return $model;
    }

    /**
     * @throws EventNotFoundException
     */
    abstract protected function getEvent(): ModelEvent;
}
