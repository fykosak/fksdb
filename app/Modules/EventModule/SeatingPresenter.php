<?php

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\Controls\Fyziklani\Seating\SeatingControl;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\ORM\Services\Fyziklani\ServiceFyziklaniTeamPosition;
use FKSDB\Models\UI\PageTitle;
use Nette\DeprecatedException;

/**
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
class SeatingPresenter extends BasePresenter {

    private ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition;

    final public function injectServiceFyziklaniTeamPosition(ServiceFyziklaniTeamPosition $serviceFyziklaniTeamPosition): void {
        $this->serviceFyziklaniTeamPosition = $serviceFyziklaniTeamPosition;
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titleDefault(): void {
        $this->setPageTitle(new PageTitle(_('Rooming'), 'fa fa-arrows'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titleEdit(): void {
        $this->setPageTitle(new PageTitle(_('Edit routing'), 'fa fa-pencil'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titleDownload(): void {
        $this->setPageTitle(new PageTitle(_('Download routing'), 'fa fa-download'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titleList(): void {
        $this->setPageTitle(new PageTitle(_('List of all teams'), 'fa fa-print'));
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function titlePreview(): void {
        $this->setPageTitle(new PageTitle(_('Preview'), 'fa fa-search'));
    }

    /**
     * @return bool
     * @throws EventNotFoundException
     */
    protected function isEnabled(): bool {
        return $this->getEvent()->event_type_id === 1;
    }

    public function authorizedEdit(): void {
        $this->setAuthorized(false);
        // $this->setAuthorized(($this->eventIsAllowed('event.seating', 'edit')));
    }

    public function authorizedDownload(): void {
        $this->setAuthorized(false);
        // $this->setAuthorized(($this->eventIsAllowed('event.seating', 'download')));
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedPreview(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('event.seating', 'preview'));
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedList(): void {
        $this->setAuthorized($this->isContestsOrgAuthorized('event.seating', 'list'));
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): void {
        $download = $this->isContestsOrgAuthorized('event.seating', 'download');
        $edit = $this->isContestsOrgAuthorized('event.seating', 'edit');
        $this->setAuthorized($download || $edit);
    }


    public function renderEdit(): void {
        throw new DeprecatedException();
        /* if ($this->isAjax()) {
             $data = $this->getHttpRequest()->getPost('requestData');
             $updatedTeams = $this->serviceFyziklaniTeamPosition->updateRouting($data);
             $response = new ReactResponse();
             $response->setAct('update-teams');
             $response->setData(['updatedTeams' => $updatedTeams]);
             $response->addMessage(new Message(_('Changes has been saved'), Message::LVL_SUCCESS));
             $this->sendResponse($response);
         }*/
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    public function renderList(): void {
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
     * @return void
     * @throws EventNotFoundException
     */
    public function renderPreview(): void {
        $this->template->event = $this->getEvent();
    }

    protected function createComponentSeating(): SeatingControl {
        return new SeatingControl($this->getContext());
    }
}
