<?php

namespace FKSDB\Components\DatabaseReflection\Links;

use FKSDB\ORM\Models\ModelEventParticipant;
use Nette\Application\BadRequestException;

/**
 * Class ParticipantDetailLink
 * @package FKSDB\Components\DatabaseReflection\Links
 */
class ParticipantDetailLink extends AbstractLink {

    /**
     * @inheritDoc
     */
    public function getText(): string {
        return _('Detail');
    }

    /**
     * @param ModelEventParticipant $model
     * @inheritDoc
     */
    public function getDestination($model): string {
        try {
            $model->getFyziklaniTeam();
            return ':Event:TeamApplication:detail';
        } catch (BadRequestException$exception) {
            return ':Event:Application:detail';
        }
    }

    /**
     * @param ModelEventParticipant $model
     * @inheritDoc
     */
    public function prepareParams($model): array {
        try {
            $team = $model->getFyziklaniTeam();
            return [
                'eventId' => $model->event_id,
                'id' => $team->e_fyziklani_team_id,
            ];
        } catch (BadRequestException$exception) {
            return [
                'eventId' => $model->event_id,
                'id' => $model->event_participant_id,
            ];
        }
    }

    /**
     * @inheritDoc
     */
    public function getModelClassName(): string {
        return ModelEventParticipant::class;
    }
}
