<?php

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Models\Entity\ModelNotFoundException;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\Exceptions\BadTypeException;
use FKSDB\Models\ORM\Models\AbstractModelSingle;
use FKSDB\Models\ORM\Models\IEventReferencedModel;
use FKSDB\Models\ORM\Models\ModelEvent;
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
    protected function getEntity(): AbstractModelSingle {
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
