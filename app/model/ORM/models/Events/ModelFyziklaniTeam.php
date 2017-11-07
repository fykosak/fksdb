<?php

namespace ORM\Models\Events;

use AbstractModelSingle;
use DbNames;
use ModelFyziklaniSubmit;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property string name
 * @property integer e_fyziklani_team_id
 * @property string room
 * @property string category
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

}
