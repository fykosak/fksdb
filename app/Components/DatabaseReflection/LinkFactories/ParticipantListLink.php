<?php

namespace FKSDB\Components\DatabaseReflection\LinkFactories;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEvent;

/**
 * Class ParticipantListLink
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ParticipantListLink extends AbstractLink {

    public function getText(): string {
        return _('List of applications');
    }

    /**
     * @param AbstractModelSingle|ModelEvent $model
     * @return string
     */
    public function getDestination(AbstractModelSingle $model): string {
        if ($model->isTeamEvent()) {
            return ':Event:TeamApplication:list';
        } else {
            return ':Event:Application:list';
        }
    }

    /**
     * @param AbstractModelSingle|ModelEvent $model
     * @return array
     */
    public function prepareParams(AbstractModelSingle $model): array {
        return [
            'eventId' => $model->event_id,
        ];
    }
}
