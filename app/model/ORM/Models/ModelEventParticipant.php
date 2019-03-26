<?php

namespace FKSDB\ORM\Models;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Payment\Price;
use FKSDB\Transitions\IEventReferencedModel;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;
use Nette\DateTime;
use Nette\InvalidStateException;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property ActiveRow person
 * @property integer event_participant_id
 * @property integer event_id
 * @property ActiveRow event
 * @property integer person_id
 * @property string note poznámka
 * @property string status
 * @property DateTime created čas vytvoření přihlášky
 * @property integer accomodation
 * @property string diet speciální stravování
 * @property string health_restrictions alergie, léky, úrazy
 * @property string tshirt_size
 * @property string tshirt_color
 * @property float price DECIMAL(6,2) vypočtená cena
 * @property string arrival_time Čas příjezdu
 * @property string arrival_destination Místo prijezdu
 * @property boolean arrival_ticket společný lístek na cestu tam
 * @property string departure_time Čas odjezdu
 * @property string departure_destination Místo odjezdu
 * @property boolean departure_ticket společný lístek na cestu zpět
 * @property boolean swimmer plavec?
 * @property string used_drugs užívané léky
 * @property string schedule
 */
class ModelEventParticipant extends AbstractModelSingle implements IEventReferencedModel {
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
     * @return Price|null
     */
    public function getPrice() {
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
