<?php

declare(strict_types=1);

namespace FKSDB\Modules\CoreModule;

use FKSDB\Models\ORM\Models\ContestantModel;
use FKSDB\Models\ORM\Models\LoginModel;
use FKSDB\Models\ORM\Models\PersonModel;
use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\PageTitle;
use Fykosak\Utils\UI\Title;

final class DispatchPresenter extends BasePresenter
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
        $person = $this->getLoggedPerson();
        $this->template->contestants = $this->getAllContestants($person);
        $this->template->organizers = $this->getAllOrganizers($person->getLogin());
        $this->template->payments = [new NavItem(
            new Title(null, _('Payments'), 'fas fa-credit-card'),
            ':Event:Payments:create',
            ['eventId' => 180]
        )];
    }

    /**
     * @phpstan-return NavItem[]
     */
    private function getAllContestants(PersonModel $person): array
    {
        $result = [];

        $result[] = new NavItem(
            new Title(null, _('Register'), 'fas fa-user-plus'),
            ':Core:Register:default'
        );
        $result[] = new NavItem(
            new Title(null, _('My applications'), 'fas fa-calendar-days'),
            ':Profile:MyApplications:default'
        );
        $result[] = new NavItem(
            new Title(null, _('My Profile'), 'fas fa-user'),
            ':Profile:Dashboard:default'
        );
        /** @var ContestantModel $contestant */
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
        return $result;
    }

    /**
     * @phpstan-return NavItem[]
     */
    private function getAllOrganizers(LoginModel $login): array
    {
        $results = [];
        foreach ($login->person->getActiveOrganizers() as $contestId => $organizer) {
            $results[$contestId] = new NavItem(
                new Title(
                    null,
                    sprintf(_('Organizer %s'), $organizer->contest->name),
                    'icon icon-' . $organizer->contest->getContestSymbol()
                ),
                ':Organizer:Dashboard:default',
                [
                    'contestId' => $contestId,
                ]
            );
        }
        $results[] = new NavItem(
            new Title(null, _('Events'), 'fas fa-calendar-days'),
            ':Event:Dispatch:default'
        );
        return $results;
    }
}
