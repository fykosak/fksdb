<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\ServicesMulti\Events;

use Fykosak\NetteORM\Exceptions\ModelException;
use FKSDB\Models\ORM\ModelsMulti\AbstractModelMulti;
use FKSDB\Models\ORM\Services\Fyziklani\ParticipantService;
use FKSDB\Models\ORM\Services\ServiceEventParticipant;
use FKSDB\Models\ORM\ModelsMulti\Events\ModelMFyziklaniParticipant;
use FKSDB\Models\ORM\ServicesMulti\AbstractServiceMulti;

class ServiceMFyziklaniParticipant extends AbstractServiceMulti
{

    public function __construct(ServiceEventParticipant $mainService, ParticipantService $joinedService)
    {
        parent::__construct($mainService, $joinedService, 'event_participant_id', ModelMFyziklaniParticipant::class);
    }

    /**
     * Delete post contact including the address.
     * @throws ModelException
     */
    public function dispose(AbstractModelMulti $model): void
    {
        parent::dispose($model);
        $this->mainService->dispose($model->mainModel);
    }
}
