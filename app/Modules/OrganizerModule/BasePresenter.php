<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use FKSDB\Modules\Core\PresenterTraits\PresenterRole;
use FKSDB\Modules\Core\PresenterTraits\SeriesPresenterTrait;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
use Fykosak\Utils\UI\Title;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Security\Resource;

abstract class BasePresenter extends \FKSDB\Modules\Core\BasePresenter
{
    use SeriesPresenterTrait;

    /**
     * @throws UnsupportedLanguageException
     * @throws BadRequestException
     * @throws ForbiddenRequestException
     */
    protected function startup(): void
    {
        parent::startup();
        if (!$this->getLoggedPerson() || !count($this->getLoggedPerson()->getActiveOrganizers())) {
            throw new ForbiddenRequestException();
        }
        $this->seriesTraitStartup();
    }

    protected function getNavRoots(): array
    {
        return [
            [
                'title' => new Title(null, _('Series')),
                'items' => [
                    'Organizer:Inbox:list' => [],
                    'Organizer:Inbox:inbox' => [],
                    'Organizer:Inbox:corrected' => [],
                    'Organizer:Points:preview' => [],
                    'Organizer:Points:entry' => [],
                ],
            ],
            [
                'title' => new Title(null, _('Tasks')),
                'items' => [
                    'Organizer:Tasks:dispatch' => [],
                    'Organizer:Tasks:import' => [],
                    'Organizer:Tasks:list' => [],
                ],
            ],
            [
                'title' => new Title(null, _('Persons')),
                'items' => [
                    'Organizer:Person:search' => [],
                    'Organizer:Person:pizza' => [],
                    'Organizer:Contestant:list' => [],
                    'Organizer:Organizer:list' => [],
                    'Organizer:Teacher:list' => [],
                ],
            ],
            [
                'title' => new Title(null, _('Entities')),
                'items' => [
                    'Organizer:Report:default' => [],
                    'Organizer:Event:list' => [],
                    'Organizer:Schools:default' => [],
                    'Organizer:StoredQuery:list' => [],
                    'Organizer:Spam:list' => [],
                ],
            ],
            [
                'title' => new Title(null, _('Others')),
                'items' => [
                    'Organizer:Chart:list' => [],
                    'Organizer:Acl:list' => [],
                ],
            ],
        ];
    }

    /**
     * @throws NoContestAvailable
     */
    protected function getStyleId(): string
    {
        $contest = $this->getSelectedContest();
        return 'contest-' . $contest->getContestSymbol();
    }

    /**
     * @throws NoContestAvailable
     * @throws NoContestYearAvailable
     */
    protected function getSubTitle(): ?string
    {
        return sprintf(_('%d. year, %s. series'), $this->getSelectedContestYear()->year, $this->getSelectedSeries());
    }

    protected function getRole(): PresenterRole
    {
        return PresenterRole::from(PresenterRole::ORGANIZER);
    }
}
