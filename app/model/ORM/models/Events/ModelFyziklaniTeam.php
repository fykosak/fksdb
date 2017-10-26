<?php

namespace ORM\Models\Events;

use AbstractModelSingle;
use DbNames;

/**
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @property string category
 * @property string room
 * @property string name
 * @property integer e_fyziklani_team_id
 */
class ModelFyziklaniTeam extends AbstractModelSingle {

    public function __toString() {
        return $this->name;
    }
    /**
     *
     * @return \ModelBrawlSubmit[]
     */
    public function getSubmits() {
        $result = array();
        foreach ($this->related(DbNames::TAB_FYZIKLANI_SUBMIT, 'e_fyziklani_team_id') as $row) {
            $result[] = ModelFyziklaniSubmit::createFromTableRow($row);
        }
        return $result;
    }

}
