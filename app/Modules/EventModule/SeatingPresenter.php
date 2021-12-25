<?php

declare(strict_types=1);

namespace FKSDB\Modules\EventModule;

use FKSDB\Components\PDFGenerators\Providers\ProviderComponent;
use FKSDB\Components\PDFGenerators\TeamSeating\SingleTeam\PageComponent;
use FKSDB\Models\Events\Exceptions\EventNotFoundException;
use FKSDB\Models\ORM\Models\Fyziklani\ModelFyziklaniTeam;
use Fykosak\Utils\UI\PageTitle;

class SeatingPresenter extends BasePresenter
{

    public function titleDefault(): PageTitle
    {
        return new PageTitle(_('Rooming'), 'fa map-marked-alt');
    }

    public function titleList(): PageTitle
    {
        return new PageTitle(_('List of all teams'), 'fa fa-print');
    }

    public function titlePreview(): PageTitle
    {
        return new PageTitle(_('Preview'), 'fa fa-search');
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

    /**
     * @throws EventNotFoundException
     */
    protected function isEnabled(): bool
    {
        return $this->getEvent()->event_type_id === 1;
    }

    /**
     * @throws EventNotFoundException
     */
    protected function createComponentSeatingList(): ProviderComponent
    {
        return new ProviderComponent(
            new PageComponent($this->getContext()),
            $this->getEvent()->getTeams()->limit(5),
            $this->getContext()
        );
    }

    protected function createComponentSeatingPreviewAll(): ProviderComponent
    {
        return new ProviderComponent(
            new \FKSDB\Components\PDFGenerators\TeamSeating\AllTeams\PageComponent('all', $this->getContext()),
            [null],
            $this->getContext()
        );
    }

    protected function createComponentSeatingPreviewEmpty(): ProviderComponent
    {
        return new ProviderComponent(
            new \FKSDB\Components\PDFGenerators\TeamSeating\AllTeams\PageComponent('empty', $this->getContext()),
            [null],
            $this->getContext()
        );
    }

    protected function createComponentSeatingPreviewDev(): ProviderComponent
    {
        return new ProviderComponent(
            new \FKSDB\Components\PDFGenerators\TeamSeating\AllTeams\PageComponent('dev', $this->getContext()),
            [null],
            $this->getContext()
        );
    }
}
