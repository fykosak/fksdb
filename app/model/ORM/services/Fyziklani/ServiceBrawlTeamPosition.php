<?php

use Authorization\ContestAuthorizator;
use Nette\Application\BadRequestException;
use ORM\Models\Events\ModelFyziklaniTeam;

class ServiceBrawlTeamPosition extends \AbstractServiceSingle {

    protected $tableName = \DbNames::TAB_BRAWL_TEAM_POSITION;
    protected $modelClassName = 'ModelBrawlTeamPosition';

    /**
     * @param $teamId
     * @return ModelBrawlTeamPosition
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
     * @param ContestAuthorizator $authorizator
     * @return string[]
     */
    public function updateRouting($data, ContestAuthorizator $authorizator) {
        $updatedTeams = [];
        $this->connection->beginTransaction();
        foreach ($data as $teamData) {
            $teamData = (object)$teamData;
            try {
                $row = $this->findByTeamId($teamData->teamId);
                if ($row) {
                    $model = ModelFyziklaniTeam::createFromTableRow($row);
                } else {
                    throw new BadRequestException(_('Team neexistuje'));
                }
                if (is_numeric($teamData->x) && is_numeric($teamData->y)) {
                } else {
                    if ($model) {
                        $model->delete();
                        $updatedTeams[] = $teamData->teamId;
                    }
                }
            } catch (\Exception $e) {
                $this->connection->rollBack();
            }
        }
        foreach ($data as $teamData) {
            $teamData = (object)$teamData;
            try {
                /**
                 * @var $model \ModelBrawlTeamPosition
                 */
                $model = $this->findByTeamId($teamData->teamId);
                if (is_numeric($teamData->x) && is_numeric($teamData->y)) {

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
                }
            } catch (\Exception $e) {
                $this->connection->rollBack();
            }
        }
        $this->connection->commit();
        
        return $updatedTeams;
    }
}
