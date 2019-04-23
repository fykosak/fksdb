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
use Nette\Utils\DateTime;
use Nette\InvalidStateException;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-readActiveRow person
 * @property-readinteger event_participant_id
 * @property-readinteger event_id
 * @property-readActiveRow event
 * @property-readinteger person_id
 * @property-readstring note poznámka
 * @property-readstring status
 * @property-readDateTime created čas vytvoření přihlášky
 * @property-readinteger accomodation
 * @property-readstring diet speciální stravování
 * @property-readstring health_restrictions alergie, léky, úrazy
 * @property-readstring tshirt_size
 * @property-readstring tshirt_color
 * @property-readfloat price DECIMAL(6,2) vypočtená cena
 * @property-readstring arrival_time Čas příjezdu
 * @property-readstring arrival_destination Místo prijezdu
 * @property-readboolean arrival_ticket společný lístek na cestu tam
 * @property-readstring departure_time Čas odjezdu
 * @property-readstring departure_destination Místo odjezdu
 * @property-readboolean departure_ticket společný lístek na cestu zpět
 * @property-readboolean swimmer plavec?
 * @property-readstring used_drugs užívané léky
 * @property-readstring schedule
 */
class ModelEventParticipant extends AbstractModelSingle implements IEventReferencedModel, IPaymentModel {
    /**
     * @return ModelPerson|null
     */
    public function getPerson() {
        if (!$this->person) {
            return null;
        }
        return ModelPerson::createFromTableRow($this->person);
    }

    /**
     * @return string
     */
    public function __toString(): string {
        if (!$this->getPerson()) {
            throw new InvalidStateException(\sprintf(_('Missing person in application Id %s.'), $this->getPrimary(false)));
        }
        return $this->getPerson()->__toString();
    }

    /**
     * @return ModelEvent
     */
    public function getEvent(): ModelEvent {
        return ModelEvent::createFromTableRow($this->event);
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
        return ModelFyziklaniTeam::createFromTableRow($row);
    }
}
