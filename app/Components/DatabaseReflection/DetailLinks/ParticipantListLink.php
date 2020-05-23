<?php

namespace FKSDB\Components\DatabaseReflection\Links;

use EventModule\BasePresenter;
use FKSDB\ORM\Models\ModelEvent;

/**
 * Class ParticipantListLink
 * @package FKSDB\Components\DatabaseReflection\Links
 */
class ParticipantListLink extends AbstractLink {

    public function getText(): string {
        return _('List of applications');
    }

    /**
     * @param ModelEvent $model
     * @inheritDoc
     */
    public function getDestination($model): string {
        if (in_array($model->event_type_id, BasePresenter::TEAM_EVENTS)) {
            return ':Event:TeamApplication:list';
        } else {
            return ':Event:Application:list';
        }
    }

    /**
     * @param ModelEvent $model
     * @inheritDoc
     */
    public function prepareParams($model): array {
        return [
            'eventId' => $model->event_id,
        ];
    }

    public function getModelClassName(): string {
        return ModelEvent::class;
    }
}
