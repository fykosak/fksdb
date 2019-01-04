<?php

namespace ORM\Models\Events;

use AbstractModelSingle;
use DbNames;
use ModelFyziklaniSubmit;
use Nette\DateTime;

/**
 * @property string category
 * @property string name
 * @property integer e_fyziklani_team_id
 * @property integer event_id
 * @property integer points
 * @property string status
 * @property DateTime created
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @author Michal Červeňák <miso@fykos.cz>
 *
 */
class ModelFyziklaniTeam extends AbstractModelSingle {

    public function __toString() {
        return $this->name;
    }

    /**
     *
     * @return ModelFyziklaniSubmit[]
     */
    public function getSubmits() {
        $result = [];
        foreach ($this->related(DbNames::TAB_FYZIKLANI_SUBMIT, 'e_fyziklani_team_id') as $row) {
            $result[] = ModelFyziklaniSubmit::createFromTableRow($row);
        }
        return $result;
    }

    /**
     * @return null|\ModelFyziklaniTeamPosition
     */
    public function getPosition() {
        $row = $this->related(DbNames::TAB_FYZIKLANI_TEAM_POSITION, 'e_fyziklani_team_id')->fetch();
        if ($row) {
            return \ModelFyziklaniTeamPosition::createFromTableRow($row);
        }
        return null;
    }

    public function hasOpenSubmit() {
        $points = $this->points;
        return !is_numeric($points);
    }

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
