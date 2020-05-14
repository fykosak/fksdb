<?php

namespace FKSDB\ORM\Models;

use FKSDB\Events\Model\Holder\Holder;
use FKSDB\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;
use Nette\DeprecatedException;
use Nette\InvalidStateException;
use Nette\Security\IResource;
use Nette\Utils\DateTime;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read int event_year
 * @property-read int year
 * @property-read string name
 * @property-read int event_id
 * @property-read ActiveRow event_type
 * @property-read int event_type_id
 * @property-read DateTime begin
 * @property-read DateTime end
 * @property-read DateTime registration_begin
 * @property-read DateTime registration_end
 * @property-read string parameters
 */
class ModelEvent extends AbstractModelSingle implements IResource, IContestReferencedModel {
    const RESOURCE_ID = 'event';

    /**
     * @return ModelEventType
     */
    public function getEventType(): ModelEventType {
        return ModelEventType::createFromActiveRow($this->event_type);
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
     * @param Holder $holder
     * @param string $name
     * @return mixed
     */
    public function getParameter(Holder $holder, string $name) {
        throw new DeprecatedException();
      /*  if (!$this->holder) {
            throw new InvalidStateException('Event does not have any holder assigned.');
        }
        return $holder->getParameter($name);*/
    }

    /**
     * @return string
     */
    public function getResourceId(): string {
        return self::RESOURCE_ID;
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
        $gameSetupRow = $this->related(DbNames::TAB_FYZIKLANI_GAME_SETUP, 'event_id')->fetch();
        if (!$gameSetupRow) {
            throw new NotSetGameParametersException(_('Herné parametre niesu nastavené'));
        }
        return ModelFyziklaniGameSetup::createFromActiveRow($gameSetupRow);
    }

    /**
     * @return GroupedSelection
     */
    public function getScheduleGroups(): GroupedSelection {
        return $this->related(DbNames::TAB_SCHEDULE_GROUP, 'event_id');
    }

    /**
     * @return GroupedSelection
     */
    public function getParticipants(): GroupedSelection {
        return $this->related(DbNames::TAB_EVENT_PARTICIPANT, 'event_id');
    }

    /**
     * @return GroupedSelection
     */
    public function getTeams(): GroupedSelection {
        return $this->related(DbNames::TAB_E_FYZIKLANI_TEAM, 'event_id');
    }

    /**
     * @return array
     */
    public function __toArray(): array {
        return [
            'eventId' => $this->event_id,
            'year' => $this->year,
            'eventYear' => $this->event_year,
            'begin' => $this->begin ? $this->begin->format('c') : null,
            'end' => $this->end ? $this->end->format('c') : null,
            'registration_begin' => $this->registration_begin->format('c'),
            'registration_end' => $this->registration_end->format('c'),
            'name' => $this->name,
            'event_type_id' => $this->event_type_id,
        ];
    }
}
