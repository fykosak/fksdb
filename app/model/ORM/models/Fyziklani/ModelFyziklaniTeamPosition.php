<?php

/**
 * Class ModelFyziklaniTeamPosition
 * @property integer room_id
 * @property integer e_fyziklani_team_id
 * @property integer row
 * @property integer col
 * @property ModelFyziklaniRoom room
 */
class ModelFyziklaniTeamPosition extends \AbstractModelSingle {

    public function getRoom() {
        return $this->room;
    }
}
