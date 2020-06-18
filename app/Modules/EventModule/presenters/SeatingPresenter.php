<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Fyziklani\SeatingControl;
use FKSDB\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;
use FKSDB\React\ReactResponse;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use ReactMessage;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SeatingPresenter extends BasePresenter {

    /**
     * @var ServiceFyziklaniTeamPosition
     */
    private $serviceFyziklaniTeamPosition;

    /**
     * @param ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition
     * @return void
     */
    public function injectServiceFyziklaniTeamPosition(ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition) {
        $this->serviceFyziklaniTeamPosition = $serviceFyziklaniTeamPosition;
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleDefault() {
        $this->setTitle(_('Rooming'), 'fa fa-arrows');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleEdit() {
        $this->setTitle(_('Edit routing'), 'fa fa-pencil');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleDownload() {
        $this->setTitle(_('Download routing'), 'fa fa-download');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titleList() {
        $this->setTitle(_('List of all teams'), 'fa fa-print');
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    public function titlePreview() {
        $this->setTitle(_('Preview'), 'fa fa-search');
    }

    /**
     * @return bool
     * @throws BadRequestException
     */
    protected function isEnabled(): bool {
        return $this->getEvent()->event_type_id === 1;
    }

    public function authorizedEdit() {
        $this->setAuthorized(false);
        // $this->setAuthorized(($this->eventIsAllowed('event.seating', 'edit')));
    }

    public function authorizedDownload() {
        $this->setAuthorized(false);
        // $this->setAuthorized(($this->eventIsAllowed('event.seating', 'download')));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedPreview() {
        $this->setAuthorized($this->isContestsOrgAuthorized('event.seating', 'preview'));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedList() {
        $this->setAuthorized($this->isContestsOrgAuthorized('event.seating', 'list'));
    }

    /**
     * @throws BadRequestException
     */
    public function authorizedDefault() {
        $download = $this->isContestsOrgAuthorized('event.seating', 'download');
        $edit = $this->isContestsOrgAuthorized('event.seating', 'edit');
        $this->setAuthorized($download || $edit);
    }


    /**
     * @throws AbortException
     */
    public function renderEdit() {
        if ($this->isAjax()) {
            $data = $this->getHttpRequest()->getPost('requestData');
            $updatedTeams = $this->serviceFyziklaniTeamPosition->updateRouting($data);
            $response = new ReactResponse();
            $response->setAct('update-teams');
            $response->setData(['updatedTeams' => $updatedTeams]);
            $response->addMessage(new ReactMessage(_('changes has been saved'), \FKSDB\Modules\Core\BasePresenter::FLASH_SUCCESS));
            $this->sendResponse($response);
        }
    }

    /**
     * @throws BadRequestException
     */
    public function renderList() {
        $this->template->event = $this->getEvent();
        $teams = $this->getEvent()->getTeams();
        $this->template->teams = $teams;
        $toPayAll = [];
        foreach ($teams as $row) {
            $team = ModelFyziklaniTeam::createFromActiveRow($row);
            $toPayAll[$team->getPrimary()] = $team->getScheduleRest();
        }
        $this->template->toPay = $toPayAll;
    }


    /**
     * @throws BadRequestException
     */
    public function renderPreview() {
        $this->template->event = $this->getEvent();
    }

    protected function createComponentSeating(): SeatingControl {
        return new SeatingControl($this->getContext());
    }
}
