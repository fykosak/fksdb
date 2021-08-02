<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\ServicesMulti\Events;

use FKSDB\Models\ORM\ModelsMulti\AbstractModelMulti;
use FKSDB\Models\ORM\ModelsMulti\Events\ModelMFyziklaniParticipant;
use FKSDB\Models\ORM\Services\Events\ServiceFyziklaniParticipant;
use FKSDB\Models\ORM\Services\ServiceEventParticipant;
use FKSDB\Models\ORM\ServicesMulti\AbstractServiceMulti;
use Fykosak\NetteORM\Exceptions\ModelException;

class ServiceMFyziklaniParticipant extends AbstractServiceMulti
{

    public function __construct(ServiceEventParticipant $mainService, ServiceFyziklaniParticipant $joinedService)
    {
        parent::__construct($mainService, $joinedService, 'event_participant_id', ModelMFyziklaniParticipant::class);
    }

    /**
     * Delete post contact including the address.
     * @param AbstractModelMulti $model
     * @throws ModelException
     */
    public function dispose(AbstractModelMulti $model): void
    {
        parent::dispose($model);
        $this->mainService->dispose($model->mainModel);
    }
}
