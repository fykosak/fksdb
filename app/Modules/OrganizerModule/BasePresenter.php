<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrganizerModule;

use FKSDB\Modules\Core\PresenterTraits\NoContestAvailable;
use FKSDB\Modules\Core\PresenterTraits\NoContestYearAvailable;
use FKSDB\Modules\Core\PresenterTraits\PresenterRole;
use FKSDB\Modules\Core\PresenterTraits\SeriesPresenterTrait;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
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

    /**
     * @phpstan-return string[]
     */
    protected function getNavRoots(): array
    {
        return ['Organizer.Dashboard.default'];
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

    /**
     * @param Resource|string|null $resource
     */
    protected function isAnyContestAuthorized($resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege);
    }

    protected function getRole(): PresenterRole
    {
        return PresenterRole::from(PresenterRole::ORGANIZER);
    }
}
