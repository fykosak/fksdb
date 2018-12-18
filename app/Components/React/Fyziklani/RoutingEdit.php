<?php

namespace FKSDB\Components\React\Fyziklani;

use FKSDB\ORM\ModelContest;
use FyziklaniModule\BasePresenter;
use Nette\ArgumentOutOfRangeException;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Json;
use ORM\Models\Events\ModelFyziklaniTeam;

/**
 * Class Routing
 */
class RoutingEdit extends FyziklaniModule {

    /**
     * @param ModelFyziklaniTeam $team
     * @return bool
     * @throws ArgumentOutOfRangeException
     */
    private function canRoute(ModelFyziklaniTeam $team): bool {
        $presenter = $this->getPresenter();
        if (!($presenter instanceof BasePresenter)) {
            throw new ArgumentOutOfRangeException();
        }
        Debugger::barDump($presenter->getContestAuthorizator()->isAllowed($team, 'routing', ModelContest::createFromTableRow($this->getEvent()->getEventType()->contest)));
        return $presenter->getContestAuthorizator()->isAllowed($team, 'routing', ModelContest::createFromTableRow($this->getEvent()->getEventType()->contest));
    }

    /**
     * @return string
     * @throws \Nette\Utils\JsonException
     */
    public function getData(): string {
        $teams = [];

        foreach ($this->serviceFyziklaniTeam->findPossiblyAttending($this->event->event_id) as $row) {
            $team = ModelFyziklaniTeam::createFromTableRow($row);
            $position = $team->getPosition();

            $teams[] = [
                'category' => $team->category,
                'roomId' => $position ? $position->getRoom()->room_id : '',
                'name' => $team->name,
                'status' => $team->status,
                'teamId' => $team->e_fyziklani_team_id,
                'x' => $position ? $position->col : null,
                'y' => $position ? $position->row : null,
                'canRoute' => $this->canRoute($team),
            ];
        }
        return Json::encode([
            'teams' => $teams,
            'rooms' => $this->getRooms(),
        ]);
    }

    public function getMode(): string {
        return '';
    }

    public function getComponentName(): string {
        return 'routing';
    }

    protected function getActions(): array {
        $actions = parent::getActions();
        $actions['save'] = $this->link('save!');
        return $actions;
    }

    public function handleSave() {
        $data = $this->getHttpRequest()->getPost('requestData');
        $updatedTeams = $this->serviceFyziklaniTeamPosition->updateRouting($data,$this->getPresenter()->getContestAuthorizator());
        $response = new \ReactResponse();
        $response->setAct('update-teams');
        $response->setData(['updatedTeams' => $updatedTeams]);
        $response->addMessage(new \ReactMessage(_('Zmeny boli uložené'), 'success'));
        $this->getPresenter()->sendResponse($response);
    }
}
