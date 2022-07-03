<?php

declare(strict_types=1);

namespace FKSDB\Models\ORM\Models\Fyziklani;

use FKSDB\Models\ORM\Models\ModelPerson;
use FKSDB\Models\ORM\Models\ModelPersonHistory;
use FKSDB\Models\WebService\XMLHelper;
use Fykosak\NetteORM\Model;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read int fyziklani_team_member_id
 * @property-read ActiveRow person
 * @property-read int person_id
 * @property-read int fyziklani_team_id
 * @property-read ActiveRow fyziklani_team
 */
class TeamMemberModel extends Model
{

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
        return $this->getPerson()->getHistoryByContestYear($this->getFyziklaniTeam()->getEvent()->getContestYear());
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
