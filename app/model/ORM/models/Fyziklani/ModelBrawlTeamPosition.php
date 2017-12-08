<?php

/**
 * Class ModelBrawlTeamPosition
 * @property integer room_id
 * @property integer e_fyziklani_team_id
 * @property integer row
 * @property integer col
 * @property ModelBrawlRoom room
 */
class ModelBrawlTeamPosition extends \AbstractModelSingle {

    public function getRoom() {
        return $this->room;
    }
}
