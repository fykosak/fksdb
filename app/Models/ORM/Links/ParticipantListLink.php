<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Links;

use Fykosak\NetteORM\AbstractModel;
use FKSDB\Models\ORM\Models\ModelEvent;

class ParticipantListLink extends LinkFactory {

    public function getText(): string {
        return _('List of applications');
    }

    /**
     * @param AbstractModel|ModelEvent $model
     */
    protected function getDestination(AbstractModel $model): string {
        if ($model->isTeamEvent()) {
            return ':Event:TeamApplication:list';
        } else {
            return ':Event:Application:list';
        }
    }

    /**
     * @param AbstractModel|ModelEvent $model
     */
    protected function prepareParams(AbstractModel $model): array {
        return [
            'eventId' => $model->event_id,
        ];
    }
}
