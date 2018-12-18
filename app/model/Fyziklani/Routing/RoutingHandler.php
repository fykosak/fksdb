<?php

namespace FKSDB\model\Fyziklani\Routing;

use Authorization\ContestAuthorizator;
use FKSDB\ORM\ModelEvent;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Database\Connection;
use ORM\Models\Events\ModelFyziklaniTeam;
use ORM\Services\Events\ServiceFyziklaniTeam;

class RoutingHandler {
    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var ServiceFyziklaniTeam
     */
    private $serviceFyziklaniTeam;
    /**
     * @var ContestAuthorizator
     */
    private $contestAuthorizator;
    /**
     * @var ModelEvent
     */
    private $event;
    /**
     * @var \ServiceBrawlTeamPosition
     */
    private $serviceBrawlTeamPosition;

    public function __construct(\ServiceBrawlTeamPosition $serviceBrawlTeamPosition, ContestAuthorizator $contestAuthorizator, ServiceFyziklaniTeam $serviceFyziklaniTeam, Connection $connection, ModelEvent $event) {
        $this->connection = $connection;
        $this->serviceFyziklaniTeam = $serviceFyziklaniTeam;
        $this->contestAuthorizator = $contestAuthorizator;
        $this->event = $event;
        $this->serviceBrawlTeamPosition = $serviceBrawlTeamPosition;
    }

    private function isSame(\ModelBrawlTeamPosition $modelBrawlTeamPosition = null, $data = null): bool {
        if ($modelBrawlTeamPosition == null) {
            return false;
        }
        if ($modelBrawlTeamPosition->row !== $data->row) {
            return false;
        }
        if ($modelBrawlTeamPosition->col !== $data->col) {
            return false;
        }
        if ($modelBrawlTeamPosition->room_id !== $data->room_id) {
            return false;
        }
        return true;
    }

    /**
     * @param $data
     * @return array
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     * @throws BadRequestException
     */
    public function updateRouting($data) {
        $updatedTeams = [];
        $this->connection->beginTransaction();
        foreach ($data as $key => &$teamData) {

            $teamData = (object)$teamData;

            $row = $this->serviceFyziklaniTeam->findByPrimary($teamData->teamId);
            if ($row) {
                $team = ModelFyziklaniTeam::createFromTableRow($row);
                if ($team->event_id !== $this->event->event_id) {
                    throw new ForbiddenRequestException(_('Editácia mimo eventu'));
                }
            } else {
                throw new BadRequestException(_('Team neexistuje'));
            }
            $model = $team->getPosition();
            if ($this->isSame($model, $teamData)) {
                unset($data[$key]);
                continue;
            } else {
                if (!$this->contestAuthorizator->isAllowed($model, 'routing', $this->event->getContest())) {
                    throw new ForbiddenRequestException(_('Editácia mimo eventu'));
                }
                $teamData->model = $model;
            }
        }
        foreach ($data as $teamData) {
            try {
                if (is_numeric($teamData->x) && is_numeric($teamData->y)) {
                } else {
                    if ($teamData->model) {
                        $teamData->model->delete();
                        $updatedTeams[] = $teamData->teamId;
                    }
                }
            } catch
            (\Exception $e) {
                $this->connection->rollBack();
                throw $e;
            }
        }
        foreach ($data as $teamData) {
            try {

                if (is_numeric($teamData->x) && is_numeric($teamData->y)) {

                    $data = ['e_fyziklani_team_id' => $teamData->teamId,
                        'row' => $teamData->y,
                        'col' => $teamData->x,
                        'room_id' => $teamData->roomId,];
                    if (!$teamData->model) {
                        $model = $this->serviceBrawlTeamPosition->createNew($data);
                        $this->serviceBrawlTeamPosition->save($model);
                    } else {
                        $teamData->model->update($data);
                    }

                    $updatedTeams[] = $teamData->teamId;
                }
            } catch
            (\Exception $e) {
                $this->connection->rollBack();
            }
        }
        $this->connection->commit();

        return $updatedTeams;
    }
}
