<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\PDFGenerators\TeamSeating\SingleTeam\AllTeamsProviderComponent as EachTeamProviderComponent;
use FKSDB\Components\PDFGenerators\TeamSeating\AllTeams\AllTeamsProviderComponent;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use FKSDB\Models\UI\PageTitle;

class SeatingPresenter extends BasePresenter
{

    public function titleDefault(): void
    {
        $this->setPageTitle(new PageTitle(_('Rooming'), 'fa map-marked-alt'));
    }

    public function titleList(): void
    {
        $this->setPageTitle(new PageTitle(_('List of all teams'), 'fa fa-print'));
    }

    public function titlePreview(): void
    {
        $this->setPageTitle(new PageTitle(_('Preview'), 'fa fa-search'));
    }

    /**
     * @return bool
     * @throws EventNotFoundException
     */
    protected function isEnabled(): bool
    {
        return $this->getEvent()->event_type_id === 1;
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedPreview(): void
    {
        $this->setAuthorized($this->isContestsOrgAuthorized('event.seating', 'preview'));
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedList(): void
    {
        $this->setAuthorized($this->isContestsOrgAuthorized('event.seating', 'list'));
    }

    /**
     * @throws EventNotFoundException
     */
    public function authorizedDefault(): void
    {
        $download = $this->isContestsOrgAuthorized('event.seating', 'download');
        $edit = $this->isContestsOrgAuthorized('event.seating', 'edit');
        $this->setAuthorized($download || $edit);
    }

    /**
     * @return void
     * @throws EventNotFoundException
     */
    final public function renderList(): void
    {
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

    protected function createComponentSeatingList(): EachTeamProviderComponent
    {
        return new EachTeamProviderComponent($this->getEvent(), $this->getContext());
    }

    protected function createComponentSeatingPreviewAll(): AllTeamsProviderComponent
    {
        return new AllTeamsProviderComponent($this->getEvent(), 'all', $this->getContext());
    }

    protected function createComponentSeatingPreviewEmpty(): AllTeamsProviderComponent
    {
        return new AllTeamsProviderComponent($this->getEvent(), 'empty', $this->getContext());
    }

    protected function createComponentSeatingPreviewDev(): AllTeamsProviderComponent
    {
        return new AllTeamsProviderComponent($this->getEvent(), 'dev', $this->getContext());
    }
}
