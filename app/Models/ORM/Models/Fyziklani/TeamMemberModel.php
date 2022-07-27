<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Models\ModelEvent;
use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Models\ModelPersonHistory;
use FKSDB\Models\ORM\Models\ModelSchool;
use FKSDB\Models\WebService\XMLHelper;
use Fykosak\NetteORM\Model;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read int fyziklani_team_member_id
 * @property-read ModelPerson person
 * @property-read int person_id
 * @property-read int fyziklani_team_id
 * @property-read TeamModel2 fyziklani_team
 */
class TeamMemberModel extends Model
{
    public function getEvent(): ModelEvent
    {
        return $this->getFyziklaniTeam()->event;
    }

    public function getPerson(): ModelPerson
    {
        return ModelPerson::createFromActiveRow($this->person);
    }

    public function getFyziklaniTeam(): TeamModel2
    {
        return TeamModel2::createFromActiveRow($this->fyziklani_team);
    }

    public function getPersonHistory(): ?ModelPersonHistory
    {
        return $this->getPerson()->getHistoryByContestYear($this->getFyziklaniTeam()->event->getContestYear());
    }

    public function getSchool(): ?ModelSchool
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
