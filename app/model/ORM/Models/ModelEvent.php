<?php

namespace FKSDB\ORM\Models;

use Events\Model\Holder\Holder;
use FKSDB\model\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;
use Nette\Utils\DateTime;
use Nette\InvalidStateException;
use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read integer event_year
 * @property-read integer year
 * @property-read string name
 * @property-read integer event_id
 * @property-read ActiveRow event_type
 * @property-read integer event_type_id
 * @property-read DateTime begin
 * @property-read DateTime end
 * @property-read DateTime registration_begin
 * @property-read DateTime registration_end
 */
class ModelEvent extends AbstractModelSingle implements IResource {

    /**
     * Event can have a holder assigned for purposes of parameter parsing.
     * Nothing else (currently).
     * @var Holder
     */
    private $holder;

    /**
     * @param Holder $holder
     */
    function setHolder(Holder $holder) {
        $this->holder = $holder;
    }

    /**
     * @return ModelEventType
     */
    public function getEventType(): ModelEventType {
        return ModelEventType::createFromActiveRow($this->event_type);
    }

    /**
     * @return ModelEventAccommodation[]
     */
    public function getEventAccommodationsAsArray(): array {
        $data = [];
        foreach ($this->related(DbNames::TAB_EVENT_ACCOMMODATION) as $item) {
            $data[] = ModelEventAccommodation::createFromActiveRow($item);
        }
        return $data;
    }

    /**
     * @return ModelContest
     */
    public function getContest(): ModelContest {
        return ModelContest::createFromActiveRow($this->getEventType()->ref(DbNames::TAB_CONTEST, 'contest_id'));
    }

    /**
     * Syntactic sugar.
     *
     * @return int
     */
    public function getAcYear(): int {
        return $this->getContest()->related('contest_year')->where('year', $this->year)->fetch()->ac_year;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getParameter($name) {
        if (!$this->holder) {
            throw new InvalidStateException('Event does not have any holder assigned.');
        }
        return $this->holder->getParameter($name);
    }

    /**
     * @return string
     */
    public function getResourceId(): string {
        return 'event';
    }

    /**
     * @return string
     */
    public function __toString(): string {
        return $this->name;
    }

    /**
     * @return ModelFyziklaniGameSetup
     * @throws NotSetGameParametersException
     */
    public function getFyziklaniGameSetup(): ModelFyziklaniGameSetup {
        $gameSetup = $this->related(DbNames::TAB_FYZIKLANI_GAME_SETUP, 'event_id')->fetch();
        if (!$gameSetup) {
            throw new NotSetGameParametersException(_('Herné parametre niesu nastavené'), 404);
        }
        return ModelFyziklaniGameSetup::createFromActiveRow($gameSetup);
    }

    /**
     * @return GroupedSelection
     */
    public function getScheduleGroups(): GroupedSelection {
        return $this->related(DbNames::TAB_SCHEDULE_GROUP);
    }

    /**
     * @return array
     */
    public function __toArray(): array {
        return [
            'eventId' => $this->event_id,
            'year' => $this->year,
            'eventYear' => $this->event_year,
            'begin' => $this->begin->format('c'),
            'end' => $this->end->format('c'),
            'registration_begin' => $this->registration_begin->format('c'),
            'registration_end' => $this->registration_end->format('c'),
            'name' => $this->name,
            'event_type_id' => $this->event_type_id,
        ];
    }

}
