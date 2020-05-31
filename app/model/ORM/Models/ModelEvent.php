<?php

namespace FKSDB\ORM\Models;

use FKSDB\Fyziklani\NotSetGameParametersException;
use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniGameSetup;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;
use Nette\Security\IResource;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property-read int event_year
 * @property-read int year
 * @property-read string name
 * @property-read int event_id
 * @property-read ActiveRow event_type
 * @property-read int event_type_id
 * @property-read \DateTimeInterface begin
 * @property-read \DateTimeInterface end
 * @property-read \DateTimeInterface registration_begin
 * @property-read \DateTimeInterface registration_end
 * @property-read string parameters
 */
class ModelEvent extends AbstractModelSingle implements IResource, IContestReferencedModel {

    public const TEAM_EVENTS = [1, 9, 13];

    public const RESOURCE_ID = 'event';

    public function getEventType(): ModelEventType {
        return ModelEventType::createFromActiveRow($this->event_type);
    }

    public function getContest(): ModelContest {
        return $this->getEventType()->getContest();
    }

    public function getAcYear(): int {
        return $this->getContest()->related('contest_year')->where('year', $this->year)->fetch()->ac_year;
    }

    public function getResourceId(): string {
        return self::RESOURCE_ID;
    }

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

    public function getScheduleGroups(): GroupedSelection {
        return $this->related(DbNames::TAB_SCHEDULE_GROUP, 'event_id');
    }

    public function getParticipants(): GroupedSelection {
        return $this->related(DbNames::TAB_EVENT_PARTICIPANT, 'event_id');
    }

    public function getTeams(): GroupedSelection {
        return $this->related(DbNames::TAB_E_FYZIKLANI_TEAM, 'event_id');
    }

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
