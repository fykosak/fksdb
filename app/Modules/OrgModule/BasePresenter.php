<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Modules\Core\PresenterTraits\SeriesPresenterTrait;
use Nette\Security\Resource;

abstract class BasePresenter extends AuthenticatedPresenter
{
    use SeriesPresenterTrait;

    protected function startup(): void
    {
        parent::startup();
        $this->seriesTraitStartup();
    }

    protected function getNavRoots(): array
    {
        return ['Org.Dashboard.default'];
    }

    protected function beforeRender(): void
    {
        $contest = $this->getSelectedContest();
        if (isset($contest) && $contest) {
            $this->getPageStyleContainer()->styleId = $contest->getContestSymbol();
            $this->getPageStyleContainer()->setNavBarClassName('navbar-dark bg-' . $contest->getContestSymbol());
            $this->getPageStyleContainer()->setNavBrandPath('/images/logo/white.svg');
        }
        parent::beforeRender();
    }

    protected function getDefaultSubTitle(): ?string
    {
        return sprintf(_('%d. year, %s. series'), $this->getSelectedContestYear()->year, $this->getSelectedSeries());
    }

    protected function isAnyContestAuthorized(Resource|string|null $resource, ?string $privilege): bool
    {
        return $this->contestAuthorizator->isAllowedForAnyContest($resource, $privilege);
    }

    protected function getRole(): string
    {
        return 'org';
    }
}
