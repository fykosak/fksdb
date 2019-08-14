<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Payment\IPaymentModel;
use FKSDB\Payment\Price;
use FKSDB\Transitions\IEventReferencedModel;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidStateException;
use Nette\Utils\DateTime;
use function sprintf;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read ActiveRow person
 * @property-read integer event_participant_id
 * @property-read integer event_id
 * @property-read ActiveRow event
 * @property-read integer person_id
 * @property-read string note poznámka
 * @property-read string status
 * @property-read DateTime created čas vytvoření přihlášky
 * @property-read integer accomodation
 * @property-read string diet speciální stravování
 * @property-read string health_restrictions alergie, léky, úrazy
 * @property-read string tshirt_size
 * @property-read string tshirt_color
 * @property-read float price DECIMAL(6,2) vypočtená cena
 * @property-read string arrival_time Čas příjezdu
 * @property-read string arrival_destination Místo prijezdu
 * @property-read boolean arrival_ticket společný lístek na cestu tam
 * @property-read string departure_time Čas odjezdu
 * @property-read string departure_destination Místo odjezdu
 * @property-read boolean departure_ticket společný lístek na cestu zpět
 * @property-read boolean swimmer plavec?
 * @property-read string used_drugs užívané léky
 * @property-read string schedule
 */
class ModelEventParticipant extends AbstractModelSingle implements IEventReferencedModel, IPaymentModel, IPersonReferencedModel {
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
     */
    public function __toString(): string {
        if (!$this->getPerson()) {
            throw new InvalidStateException(sprintf(_('Missing person in application Id %s.'), $this->getPrimary(false)));
        }
        return $this->getPerson()->__toString();
    }

    /**
     * @return ModelEvent
     */
    public function getEvent(): ModelEvent {
        return ModelEvent::createFromActiveRow($this->event);
    }

    /**
     * @return Price
     */
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
}
