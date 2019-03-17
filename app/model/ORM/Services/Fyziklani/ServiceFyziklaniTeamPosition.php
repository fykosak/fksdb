<?php

namespace FKSDB\ORM\Services\Fyziklani;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeamPosition;
use Traversable;

/**
 * Class FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition
 */
class ServiceFyziklaniTeamPosition extends AbstractServiceSingle {

    protected $tableName = DbNames::TAB_FYZIKLANI_TEAM_POSITION;
    protected $modelClassName = 'FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeamPosition';

    /**
     * @param int $teamId
     * @return ModelFyziklaniTeamPosition
     */
    public function findByTeamId(int $teamId) {
        $row = $this->getTable()->where('e_fyziklani_team_id', $teamId)->fetch();
        if ($row) {
            return ModelFyziklaniTeamPosition::createFromTableRow($row);
        }
        return null;
    }

    /**
     * @param Traversable $data
     * @return string[]
     */
    public function updateRouting(Traversable $data) {
        $updatedTeams = [];
        foreach ($data as $teamData) {
            $teamData = (object)$teamData;
            try {
                /**
                 * @var \FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeamPosition $model
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
