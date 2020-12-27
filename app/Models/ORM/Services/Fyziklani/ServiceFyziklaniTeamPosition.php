<?php

namespace FKSDB\Models\ORM\Services\Fyziklani;



use FKSDB\Models\ORM\DbNames;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeamPosition;
use FKSDB\Models\ORM\Services\AbstractServiceSingle;
use FKSDB\Models\ORM\Tables\TypedTableSelection;
use Nette\Database\Context;
use Nette\Database\IConventions;

/**
 * Class ServiceFyziklaniTeamPosition
 * @author Michal Červeňák <miso@fykos.cz>
 */
class ServiceFyziklaniTeamPosition extends AbstractServiceSingle {


    public function __construct(Context $connection, IConventions $conventions) {
        parent::__construct($connection, $conventions, DbNames::TAB_FYZIKLANI_TEAM_POSITION, ModelFyziklaniTeamPosition::class);
    }

    public function findByTeamId(int $teamId): ?ModelFyziklaniTeamPosition {
        /** @var ModelFyziklaniTeamPosition $row */
        $row = $this->getTable()->where('e_fyziklani_team_id', $teamId)->fetch();
        return $row ? $row : null;
    }

    public function updateRouting(array $data): array {
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
                } elseif ($model) {
                    $model->delete();
                    $updatedTeams[] = $teamData->teamId;
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
