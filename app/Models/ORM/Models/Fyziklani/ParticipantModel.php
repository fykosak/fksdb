<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Models\EventParticipantModel;
use Fykosak\NetteORM\Model;

/**
 * @property-read EventParticipantModel event_participant
 * @property-read int event_participant_id
 * @property-read int e_fyziklani_team_id
 * @property-read TeamModel e_fyziklani_team
 * @deprecated
 */
class ParticipantModel extends Model
{
}
