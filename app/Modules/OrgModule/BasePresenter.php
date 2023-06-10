<?php

declare(strict_types=1);

namespace FKSDB\Modules\OrgModule;

use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Modules\Core\PresenterTraits\PresenterRole;
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
            $this->getPageStyleContainer()->styleIds[] = $contest->getContestSymbol();
            $this->getPageStyleContainer()->navBarClassName = 'navbar-dark bg-' . $contest->getContestSymbol();
            $this->getPageStyleContainer()->navBrandPath = '/images/logo/white.svg';
        }
        parent::beforeRender();
    }

    protected function getDefaultSubTitle(): ?string
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
        return PresenterRole::tryFrom(PresenterRole::ORG);
    }
}
