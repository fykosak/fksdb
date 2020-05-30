<?php

namespace FKSDB\Components\DatabaseReflection\Links;

use EventModule\BasePresenter;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\Models\ModelEvent;

/**
 * Class ParticipantListLink
 * *
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
        if (in_array($model->event_type_id, BasePresenter::TEAM_EVENTS)) {
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

    public function getModelClassName(): string {
        return ModelEvent::class;
    }
}
