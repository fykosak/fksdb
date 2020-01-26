<?php

namespace FyziklaniModule;

use FKSDB\Components\Controls\Fyziklani\RoutingDownload;
use FKSDB\Components\Controls\Fyziklani\RoutingEdit;
use FKSDB\React\ReactResponse;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use ReactMessage;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class RoomsPresenter extends BasePresenter {

    public function titleDefault() {
        $this->setTitle(_('Rozdělení do místností'));
        $this->setIcon('fa fa-arrows');
    }

    public function titleEdit() {
        $this->setTitle(_('Edit routing'));
        $this->setIcon('fa fa-pencil');
    }

    public function titleDownload() {
        $this->setTitle(_('Download routing'));
        $this->setIcon('fa fa-download');
    }


    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedEdit() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani.rooms', 'edit')));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedDownload() {
        $this->setAuthorized(($this->eventIsAllowed('fyziklani.rooms', 'download')));
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function authorizedDefault() {
        $download = $this->eventIsAllowed('fyziklani.rooms', 'download');
        $edit = $this->eventIsAllowed('fyziklani.rooms', 'edit');
        $this->setAuthorized($download || $edit);
    }

    /**
     * @throws AbortException
     */
    public function renderEdit() {
        if ($this->isAjax()) {
            $data = $this->getHttpRequest()->getPost('requestData');
            $updatedTeams = $this->getServiceFyziklaniTeamPosition()->updateRouting($data);
            $response = new ReactResponse();
            $response->setAct('update-teams');
            $response->setData(['updatedTeams' => $updatedTeams]);
            $response->addMessage(new ReactMessage(_('Zmeny boli uložené'), \BasePresenter::FLASH_SUCCESS));
            $this->sendResponse($response);
        }
    }

    /**
     * @throws AbortException
     * @throws BadRequestException
     */
    public function renderPreview() {
        $this->template->places = [
            [820, 840],

            [860, 800],
            [860, 880],

            [900, 760],
            [900, 840],
            [900, 920],

            [940, 720],
            [940, 800],
            [940, 880],
            [940, 960],

            [980, 680],
            [980, 760],
            [980, 840],
            [980, 920],
            [980, 1000],

            [1020, 640],
            [1020, 720],
            [1020, 800],
            [1020, 880],
            [1020, 960],
            [1020, 1040],

            [1060, 680],
            [1060, 760],
            [1060, 840],
            [1060, 920],
            [1060, 1000],

            [1100, 720],
            [1100, 800],
            [1100, 880],
            [1100, 960],

            [1140, 760],
            [1140, 840],
            [1140, 920],

            [1180, 800],


            // sector B

            [940, 1120],

            [900, 1160],
            [900, 1080],

            [860, 1200],
            [860, 1120],
            [860, 1040],

            [820, 1240],
            [820, 1160],
            [820, 1080],
            [820, 1000],

            [780, 1200],
            [780, 1120],
            [780, 1040],
            [780, 960],

            [740, 1240],
            [740, 1160],
            [740, 1080],
            [740, 1000],
            [740, 920],

            [700, 1280],
            [700, 1200],
            [700, 1120],
            [700, 1040],
            [700, 960],

            [660, 1240],
            [660, 1160],
            [660, 1080],
            [660, 1000],

            [620, 1200],
            [620, 1120],
            [620, 1040],

            [580, 1160],
            [580, 1080],

            [540, 1120],

            // D
            [1040, 460],

            [1000, 420],

            [960, 460],
            [960, 540],

            [920, 420],
            [920, 500],
            [920, 580],

            [880, 460],
            [880, 540],
            [880, 620],

            [840, 420],
            [840, 500],
            [840, 580],
            [840, 660],

            [800, 460],
            [800, 540],
            [800, 620],
            [800, 700],

            [760, 580],
            [760, 660],
            [760, 740],

            [720, 540],
            [720, 620],
            [720, 700],
            [720, 780],

            [680, 580],
            [680, 660],
            [680, 740],

            [640, 540],
            [640, 620],
            [640, 700],

            [600, 500],
            [600, 580],
            [600, 660],

            [560, 540],
            [560, 620],

            [520, 420],
            [520, 500],
            [520, 580],

            [480, 460],
            [480, 540],

            [440, 420],
            [440, 500],

            [400, 460],
#C

            [300,1120],
            [300,1040],
            [300,960],

            [340,1160],
        ];
        $this->template->teams = $this->getServiceFyziklaniTeamPosition()->getAllTeamsForEvents($this->getEvent());
    }

    /**
     * @return RoutingDownload
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentDownload(): RoutingDownload {
        return $this->fyziklaniComponentsFactory->createRoutingDownload($this->getEvent());
    }

    /**
     * @return RoutingEdit
     * @throws AbortException
     * @throws BadRequestException
     */
    public function createComponentRouting(): RoutingEdit {
        return $this->fyziklaniComponentsFactory->createRoutingEdit($this->getEvent());
    }
}
