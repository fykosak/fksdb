<?php

namespace FKSDB\Modules\Core\PresenterTraits;

use FKSDB\Exceptions\BadTypeException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\IEventReferencedModel;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
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
     * @return AbstractModelSingle
     * @throws AbortException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
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
     * @return ModelEvent
     * @throws BadRequestException
     * @throws AbortException
     */
    abstract protected function getEvent(): ModelEvent;

}
