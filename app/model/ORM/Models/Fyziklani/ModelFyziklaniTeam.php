<?php

namespace FKSDB\ORM\Models\Fyziklani;

use FKSDB\ORM\AbstractModelSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\ModelEvent;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Utils\DateTime;

/**
 * @property-read  string category
 * @property-read  string name
 * @property-read  integer e_fyziklani_team_id
 * @property-read  integer event_id
 * @property-read  integer points
 * @property-read  string status
 * @property-read  DateTime created
 * @property-read  string phone
 * @property-read  bool force_a
 * @property-read  string password
 * @property-read  ActiveRow event
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @author Michal Červeňák <miso@fykos.cz>
 *
 */
class ModelFyziklaniTeam extends AbstractModelSingle {

    /**
     * @return string
     */
    public function __toString(): string {
        return $this->name;
    }

    /**
     * @return ModelEvent
     */
    public function getEvent(): ModelEvent {
        return ModelEvent::createFromTableRow($this->event);
    }

    /**
     * @return Selection
     */
    public function getParticipants(): Selection {
        return $this->related(DbNames::TAB_E_FYZIKLANI_PARTICIPANT, 'e_fyziklani_team_id');
    }

    /**
     * @return Selection
     */
    public function getSubmits(): Selection {
        return $this->related(DbNames::TAB_FYZIKLANI_SUBMIT, 'e_fyziklani_team_id')->where('points IS NOT NULL');
    }

    /**
     * @return null|ModelFyziklaniTeamPosition
     */
    public function getPosition() {
        $row = $this->related(DbNames::TAB_FYZIKLANI_TEAM_POSITION, 'e_fyziklani_team_id')->fetch();
        if ($row) {
            return ModelFyziklaniTeamPosition::createFromTableRow($row);
        }
        return null;
    }

    /**
     * @return bool
     */
    public function hasOpenSubmitting(): bool {
        $points = $this->points;
        return !is_numeric($points);
    }

    /**
     * @param bool $includePosition
     * @return array
     */
    public function __toArray(bool $includePosition = false): array {
        $data = [
            'created' => $this->created->format('c'),
            'category' => $this->category,
            'name' => $this->name,
            'status' => $this->status,
            'teamId' => $this->e_fyziklani_team_id,
        ];
        $position = $this->getPosition();
        if ($includePosition && $position) {
            $data['x'] = $position->col;
            $data['y'] = $position->row;
            $data['roomId'] = $position->getRoom()->room_id;
        }
        return $data;
    }

}
