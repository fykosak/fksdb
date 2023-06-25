<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

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
        $this->seriesTraitStartup();
    }

    protected function getNavRoots(): array
    {
        return ['Org.Dashboard.default'];
    }

    protected function getStyleId(): string
    {
        $contest = $this->getSelectedContest();
        if (isset($contest)) {
            return 'contest-' . $contest->getContestSymbol();
        }
        return parent::getStyleId();
    }

    protected function getSubTitle(): ?string
    {
        return sprintf(_('%d. year, %s. series'), $this->getSelectedContestYear()->year, $this->getSelectedSeries());
    }

    protected function isAnyContestAuthorized(Resource | string | null $resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowed($resource, $privilege);
    }

    protected function getRole(): PresenterRole
    {
        return PresenterRole::tryFrom(PresenterRole::ORG);
    }
}
