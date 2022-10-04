<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\WebService\XMLHelper;
use Fykosak\NetteORM\Model;

/**
 * @property-read int fyziklani_team_member_id
 * @property-read PersonModel person
 * @property-read int person_id
 * @property-read int fyziklani_team_id
 * @property-read TeamModel2 fyziklani_team
 */
class TeamMemberModel extends Model
{
    public function getPersonHistory(): ?PersonHistoryModel
    {
        return $this->person->getHistoryByContestYear($this->fyziklani_team->event->getContestYear());
    }

    public function __toArray(): array
    {
        return [
            'participantId' => $this->fyziklani_team_member_id,
            'personId' => $this->person_id,
        ];
    }

    public function createXMLNode(\DOMDocument $document): \DOMElement
    {
        $node = $document->createElement('participant');
        $node->setAttribute('eventParticipantId', (string)$this->fyziklani_team_member_id);
        XMLHelper::fillArrayToNode($this->__toArray(), $document, $node);
        return $node;
    }
}
