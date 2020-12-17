<?php

namespace FKSDB\Model\DBReflection\LinkFactories;

use FKSDB\Model\ORM\Models\AbstractModelSingle;
use FKSDB\Model\ORM\Models\ModelEvent;

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
    protected function getDestination(AbstractModelSingle $model): string {
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
    protected function prepareParams(AbstractModelSingle $model): array {
        return [
            'eventId' => $model->event_id,
        ];
    }
}
