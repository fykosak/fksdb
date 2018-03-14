<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\Fyziklani\RoutingDownload;
use FKSDB\Components\Controls\Fyziklani\RoutingEdit;
use Nette\Application\Responses\JsonResponse;
use Nette\Utils\Json;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class RoomsPresenter extends BasePresenter {

    public function titleDefault() {
        $this->setTitle(_('Rozdělení do místností'));
    }

    public function titleEdit() {
        $this->setTitle(_('Edit routing'));
    }

    public function titleDownload() {
        $this->setTitle(_('Download routing'));
    }

    public function authorizedEdit() {
        // TODO now can edit routing anybody
        $this->setAuthorized(($this->eventIsAllowed('fyziklani', 'rooms')));
    }

    public function authorizedDownload() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani', 'rooms')));
    }

    public function authorizedDefault() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani', 'rooms')));
    }

    public function renderEdit() {
        if ($this->isAjax()) {
            $data = Json::decode($this->getHttpRequest()->getPost('data'));
            $updatedTeams = $this->serviceBrawlTeamPosition->updateRouting($data);
            $this->sendResponse(new JsonResponse(['updatedTeams' => $updatedTeams]));
        }
    }

    public function createComponentDownload() {
        $control = new RoutingDownload($this->getTranslator());
        $buildings = $this->getEvent()->getParameter('buildings');
        $control->setBuildings($buildings);
        $control->setRooms($this->getRooms());
        $control->setTeams($this->serviceFyziklaniTeam->getTeams($this->getEventId()));
        return $control;
    }


    public function createComponentRouting() {
        $control = new RoutingEdit();
        $data = [
            'teams' => $this->serviceFyziklaniTeam->getTeams($this->getEventId()),
            'rooms' => $this->getRooms(),
        ];
        $control->setData($data);
        return $control;
    }
}
