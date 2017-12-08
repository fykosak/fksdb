<?php

namespace ORM\Models\Events;

use AbstractModelSingle;
use DbNames;
use ModelFyziklaniSubmit;

/**
 * @property string category
 * @property string name
 * @property integer e_fyziklani_team_id
 * @property integer event_id
 * @property integer points
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
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
     * @return null|\ModelBrawlTeamPosition
     */
    public function getPosition(){
        foreach ($this->related(DbNames::TAB_BRAWL_TEAM_POSITION, 'e_fyziklani_team_id') as $row) {
            return \ModelBrawlTeamPosition::createFromTableRow($row);
        }
        return null;
    }

}
