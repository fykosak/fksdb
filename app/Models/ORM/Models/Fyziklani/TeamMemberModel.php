<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Models\EventModel;
use FKSDB\Models\ORM\Models\PersonModel;
use FKSDB\Models\ORM\Models\PersonHistoryModel;
use FKSDB\Models\ORM\Models\SchoolModel;
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
    public function getEvent(): EventModel
    {
        return $this->getFyziklaniTeam()->event;
    }

    public function getPerson(): PersonModel
    {
        return PersonModel::createFromActiveRow($this->person);
    }

    public function getFyziklaniTeam(): TeamModel2
    {
        return TeamModel2::createFromActiveRow($this->fyziklani_team);
    }

    public function getPersonHistory(): ?PersonHistoryModel
    {
        return $this->getPerson()->getHistoryByContestYear($this->getFyziklaniTeam()->event->getContestYear());
    }

    public function getSchool(): ?SchoolModel
    {
        $history = $this->getPersonHistory();
        return $history ? $history->school : null;
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
