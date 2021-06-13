<?php

namespace FKSDB\Models\ORM\Models;

use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\Payment\Price;
use FKSDB\Models\WebService\NodeCreator;
use FKSDB\Models\WebService\XMLHelper;
use Fykosak\NetteORM\AbstractModel;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\Security\Resource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read ActiveRow person
 * @property-read int event_participant_id
 * @property-read int event_id
 * @property-read ActiveRow event
 * @property-read int person_id
 * @property-read string note poznámka
 * @property-read string status
 * @property-read \DateTimeInterface created čas vytvoření přihlášky
 * @property-read int accomodation
 * @property-read string diet speciální stravování
 * @property-read string health_restrictions alergie, léky, úrazy
 * @property-read string tshirt_size
 * @property-read string tshirt_color
 * @property-read string jumper_size
 * @property-read float price DECIMAL(6,2) vypočtená cena
 * @property-read \DateInterval arrival_time Čas příjezdu
 * @property-read string arrival_destination Místo prijezdu
 * @property-read bool arrival_ticket společný lístek na cestu tam
 * @property-read \DateInterval departure_time Čas odjezdu
 * @property-read string departure_destination Místo odjezdu
 * @property-read bool departure_ticket společný lístek na cestu zpět
 * @property-read bool swimmer plavec?
 * @property-read string used_drugs užívané léky
 * @property-read string schedule
 * @property-read int lunch_count
 */
class ModelEventParticipant extends AbstractModel implements Resource, NodeCreator {

    public const RESOURCE_ID = 'event.participant';
    public const STATE_AUTO_INVITED = 'auto.invited';
    public const STATE_AUTO_SPARE = 'auto.spare';

    public function getPerson(): ModelPerson {
        return ModelPerson::createFromActiveRow($this->person);
    }

    public function getPersonHistory(): ?ModelPersonHistory {
        return $this->getPerson()->getHistory($this->getEvent()->getAcYear());
    }

    public function getContest(): ModelContest {
        return $this->getEvent()->getContest();
    }

    public function __toString(): string {
        return $this->getPerson()->__toString();
    }

    public function getEvent(): ModelEvent {
        return ModelEvent::createFromActiveRow($this->event);
    }

    public function getPrice(): Price {
        return new Price($this->price, Price::CURRENCY_CZK);
    }

    /**
     * @return ModelFyziklaniTeam
     * @throws BadRequestException
     */
    public function getFyziklaniTeam(): ModelFyziklaniTeam {
        $row = $this->related(DbNames::TAB_E_FYZIKLANI_PARTICIPANT, 'event_participant_id')->select('e_fyziklani_team.*')->fetch();
        if (!$row) {
            throw new BadRequestException('Event is not fyziklani!');
        }
        return ModelFyziklaniTeam::createFromActiveRow($row);
    }

    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }

    public function __toArray(): array {
        return [
            'participantId' => $this->event_participant_id,
            'eventId' => $this->event_id,
            'personId' => $this->person_id,
            // 'note' => $this->note,
            'status' => $this->status,
            'created' => $this->created,
            // 'diet' => $this->diet,
            // 'healthRestrictions' => $this->health_restrictions,
            // 'tshirtSize' => $this->tshirt_size,
            // 'tshirtColor' => $this->tshirt_color,
            // 'jumperSize' => $this->jumper_size,
            // 'price' => $this->price,
            // 'arrivalTime' => $this->arrival_time,
            // 'arrivalDestination' => $this->arrival_destination,
            // 'arrivalTicket' => $this->arrival_ticket,
            // 'departureTime' => $this->departure_time,
            // 'departureDestination' => $this->departure_destination,
            // 'departureTicket' => $this->departure_ticket,
            // 'swimmer' => $this->swimmer,
            // 'usedDrugs' => $this->used_drugs,
            // 'lunchCount' => $this->lunch_count,
        ];
    }

    public function createXMLNode(\DOMDocument $document): \DOMElement {
        $node = $document->createElement('participant');
        $node->setAttribute('eventParticipantId', $this->event_participant_id);
        XMLHelper::fillArrayToNode($this->__toArray(), $document, $node);
        return $node;
    }
}
