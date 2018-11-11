<?php

namespace FKSDB\ORM;

use AbstractModelSingle;
use Nette\Database\Table\ActiveRow;
use Nette\DateTime;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property ActiveRow person
 * @property integer event_participant_id
 * @property integer event_id
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
 */
class ModelEventParticipant extends AbstractModelSingle {

    public function getPerson(): ModelPerson {
        return ModelPerson::createFromTableRow($this->person);
    }

    public function __toString() {
        if (!$this->getPerson()) {
            trigger_error("Missing person in application ID '" . $this->getPrimary(false) . "'.");
            //throw new InvalidStateException("Missing person in application ID '" . $this->getPrimary(false) . "'.");
        }
        return $this->getPerson()->getFullname();
    }

}
