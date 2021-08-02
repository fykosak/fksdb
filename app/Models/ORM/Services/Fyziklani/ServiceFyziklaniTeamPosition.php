<?php

namespace FKSDB\Models\ORM\Services\Fyziklani;

use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeamPosition;
use Fykosak\NetteORM\AbstractService;
use Fykosak\NetteORM\TypedTableSelection;

class ServiceFyziklaniTeamPosition extends AbstractService
{

    public function updateRouting(array $data): array
    {
        $updatedTeams = [];
        foreach ($data as $teamData) {
            $teamData = (object)$teamData;
            try {
                /** @var ModelFyziklaniTeamPosition $model */
                $model = $this->findByPrimary($teamData->teamId);
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
                } elseif ($model) {
                    $model->delete();
                    $updatedTeams[] = $teamData->teamId;
                }
            } catch (\Exception $exception) {
            }
        }
        return $updatedTeams;
    }

    public function getFreePlaces(array $roomIds): TypedTableSelection
    {
        return $this->getAllPlaces($roomIds)->where('e_fyziklani_team IS NULL');
    }

    public function getAllPlaces(array $roomIds): TypedTableSelection
    {
        return $this->getTable()->where('room_id', $roomIds);
    }
}
