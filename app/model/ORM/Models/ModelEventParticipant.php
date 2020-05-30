<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Payment\Price;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidStateException;
use Nette\Security\IResource;

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
 * @property-read float price DECIMAL(6,2) vypočtená cena
 * @property-read string arrival_time Čas příjezdu
 * @property-read string arrival_destination Místo prijezdu
 * @property-read bool arrival_ticket společný lístek na cestu tam
 * @property-read string departure_time Čas odjezdu
 * @property-read string departure_destination Místo odjezdu
 * @property-read bool departure_ticket společný lístek na cestu zpět
 * @property-read bool swimmer plavec?
 * @property-read string used_drugs užívané léky
 * @property-read string schedule
 */
class ModelEventParticipant extends AbstractModelSingle implements IEventReferencedModel, IPersonReferencedModel, IResource {
    const RESOURCE_ID = 'event.participant';

    /**
     * @return ModelPerson|null
     */
    public function getPerson() {
        if (!$this->person) {
            return null;
        }
        return ModelPerson::createFromActiveRow($this->person);
    }

    /**
     * @return string
     * @throws InvalidStateException
     */
    public function __toString(): string {
        if (!$this->getPerson()) {
            throw new InvalidStateException(\sprintf(_('Missing person in application Id %s.'), $this->getPrimary(false)));
        }
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
            throw new BadRequestException('Event is not fyziklani');
        }
        return ModelFyziklaniTeam::createFromActiveRow($row);
    }

    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }
}
