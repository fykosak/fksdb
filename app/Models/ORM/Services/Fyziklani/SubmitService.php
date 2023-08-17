<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Services\Fyziklani;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\Fyziklani\SubmitModel;
use Fykosak\NetteORM\Service;

/**
 * @phpstan-extends Service<SubmitModel>
 * @phpstan-import-type SerializedSubmitModel from SubmitModel
 */
final class SubmitService extends Service
{
    /**
     * @phpstan-return array<int,SerializedSubmitModel>
     */
    public function serialiseSubmits(EventModel $event, ?string $lastUpdated): array
    {
        $query = $this->getTable()->where('fyziklani_task.event_id', $event->event_id);
        $submits = [];
        if ($lastUpdated) {
            $query->where('modified >= ?', $lastUpdated);
        }
        /** @var SubmitModel $submit */
        foreach ($query as $submit) {
            $submits[$submit->fyziklani_submit_id] = $submit->__toArray();
        }
        return $submits;
    }
}
