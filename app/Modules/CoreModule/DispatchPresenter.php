<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\PageTitle;
use Fykosak\Utils\UI\Title;

class DispatchPresenter extends BasePresenter
{
    public function titleDefault(): PageTitle
    {
        return new PageTitle(null, _('Home'), 'fas fa-home');
    }

    public function authorizedDefault(): bool
    {
        return true;
    }

    final public function renderDefault(): void
    {
        /** @var LoginModel $login */
        $login = $this->getUser()->getIdentity();
        $person = $this->getLoggedPerson();
        $this->template->contestants = $person ? $this->getAllContestants($person) : [];
        $this->template->orgs = $this->getAllOrganisers($login);
    }

    private function getAllContestants(PersonModel $person): array
    {
        $result = [];
        /** @var ContestantModel $contestant */
        $result[] = new NavItem(
            new Title(null, _('Register into competition'), 'fas fa-user-plus'),
            ':Public:Register:contest'
        );
        $result[] = new NavItem(
            new Title(null, _('My applications'), 'fas fa-calendar-days'),
            ':Core:MyApplications:default'
        );
        $result[] = new NavItem(
            new Title(null, _('My Profile'), 'fas fa-user'),
            ':Profile:Dashboard:default'
        );
        foreach ($person->getContestants() as $contestant) {
            $acYear = $contestant->getContestYear()->ac_year;
            $result[] = new NavItem(
                new Title(
                    null,
                    sprintf(_('Contestant in %dth year (%d/%d)'), $contestant->year, $acYear, $acYear + 1),
                    'icon icon-' . $contestant->contest->getContestSymbol()
                ),
                ':Public:Dashboard:default',
                [
                    'contestId' => $contestant->contest_id,
                    'year' => $contestant->year,
                ]
            );
        }

        // <img n:attr="src => '/images/contests/applications.svg'" alt="" class="w-100"/>
        return $result;
    }

    private function getAllOrganisers(LoginModel $login): array
    {
        $results = [];
        foreach ($login->person->getActiveOrgs() as $contestId => $org) {
            $results[$contestId] = new NavItem(
                new Title(
                    null,
                    sprintf(_('Organizer %s'), $org->contest->name),
                    'icon icon-' . $org->contest->getContestSymbol()
                ),
                ':Org:Dashboard:default',
                [
                    'contestId' => $contestId,
                ]
            );
        }
        $results[] = new NavItem(
            new Title(null, _('Events'), 'fas fa-calendar-days'),
            ':Event:Dispatch:default'
        );
        //         <img src="/images/contests/event.gif" alt="" class="w-100"/>
        return $results;
    }

    protected function beforeRender(): void
    {
        $this->getPageStyleContainer()->navBarClassName = 'bg-dark navbar-dark';
        $this->getPageStyleContainer()->navBrandPath = '/images/logo/white.svg';
        parent::beforeRender();
    }
}
