<?php

namespace ORM\Models\Events;

use AbstractModelSingle;
use DbNames;
use ModelFyziklaniSubmit;
use Nette\Security\IResource;

/**
 * @property string category
 * @property string name
 * @property integer e_fyziklani_team_id
 * @property integer event_id
 * @property integer points
 * @property string status
 *
 * @author Michal Koutný <xm.koutny@gmail.com>
 * @author Michal Červeňák <miso@fykos.cz>
 *
 */
class ModelFyziklaniTeam extends AbstractModelSingle implements IResource {

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
    public function getPosition() {
        $row = $this->related(DbNames::TAB_BRAWL_TEAM_POSITION, 'e_fyziklani_team_id')->fetch();
        if ($row) {
            return \ModelBrawlTeamPosition::createFromTableRow($row);
        }
        return null;
    }

    public function hasOpenSubmit() {
        $points = $this->points;
        return !is_numeric($points);
    }

    public function getResourceId() {
        return 'fyziklani.team';
    }

}
