<?php

namespace FKSDB\ORM\Services\Fyziklani;

use FKSDB\ORM\AbstractServiceSingle;
use FKSDB\ORM\DbNames;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeamPosition;
use FKSDB\ORM\Tables\TypedTableSelection;
use Traversable;

/**
 * Class FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition
 */
class ServiceFyziklaniTeamPosition extends AbstractServiceSingle {

    public function getModelClassName(): string {
        return ModelFyziklaniTeamPosition::class;
    }

    protected function getTableName(): string {
        return DbNames::TAB_FYZIKLANI_TEAM_POSITION;
    }

    /**
     * @param int $teamId
     * @return ModelFyziklaniTeamPosition|null
     */
    public function findByTeamId(int $teamId) {
        /** @var ModelFyziklaniTeamPosition $row */
        $row = $this->getTable()->where('e_fyziklani_team_id', $teamId)->fetch();
        return $row ? $row : null;
    }

    /**
     * @param Traversable $data
     * @return string[]
     */
    public function updateRouting(Traversable $data): array {
        $updatedTeams = [];
        foreach ($data as $teamData) {
            $teamData = (object)$teamData;
            try {
                /** @var ModelFyziklaniTeamPosition $model */
                $model = $this->findByTeamId($teamData->teamId);
                if (is_numeric($teamData->x) && is_numeric($teamData->y)) {

                    $data = [
                        'e_fyziklani_team_id' => $teamData->teamId,
                        'row' => $teamData->y,
                        'col' => $teamData->x,
                        'room_id' => $teamData->roomId,
                    ];
                    if (!$model) {
                        $this->createNewModel($data);
                    } else {
                        $model->update($data);
                    }
                    $updatedTeams[] = $teamData->teamId;
                } else {
                    if ($model) {
                        $model->delete();
                        $updatedTeams[] = $teamData->teamId;
                    }
                }
            } catch (\Exception $exception) {
            }

        }
        return $updatedTeams;
    }

    public function getAllPlaces(array $roomIds): TypedTableSelection {
        return $this->getTable()->where('room_id', $roomIds);
    }

    public function getFreePlaces(array $roomIds): TypedTableSelection {
        return $this->getAllPlaces($roomIds)->where('e_fyziklani_team IS NULL');
    }
}
