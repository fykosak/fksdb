<?php

class ServiceBrawlTeamPosition extends \AbstractServiceSingle {

    protected $tableName = \DbNames::TAB_BRAWL_TEAM_POSITION;
    protected $modelClassName = 'ModelBrawlTeamPosition';

    /**
     * @return \ModelBrawlTeamPosition
     */
    public function findByTeamId($teamId) {
        $row = $this->getTable()->where('e_fyziklani_team_id', $teamId)->fetch();
        if ($row) {
            return ModelBrawlTeamPosition::createFromTableRow($row);
        }
        return null;
    }

    /**
     * @param $data
     * @return string[]
     */
    public function updateRouting($data) {
        $updatedTeams = [];
        foreach ($data as $teamData) {
            try {
                /**
                 * @var $model \ModelBrawlTeamPosition
                 */
                $model = $this->findByTeamId($teamData->teamId);
                if (!is_null($teamData->x) && !is_null($teamData->y)) {

                    $data = [
                        'e_fyziklani_team_id' => $teamData->teamId,
                        'row' => $teamData->y,
                        'col' => $teamData->x,
                        'room_id' => $teamData->roomId,
                    ];
                    if (!$model) {
                        $model = $this->createNew($data);
                    } else {
                        $this->updateModel($model, $data);
                    }
                    $this->save($model);
                    $updatedTeams[] = $teamData->teamId;
                } else {
                    if ($model) {
                        $model->delete();
                        $updatedTeams[] = $teamData->teamId;
                    }
                }
            } catch (\Exception $e) {
            }

        }
        return $updatedTeams;
    }

}
