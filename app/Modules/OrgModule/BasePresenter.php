<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Modules\Core\PresenterTraits\SeriesPresenterTrait;
use Nette\Security\Resource;

abstract class BasePresenter extends AuthenticatedPresenter {

    use SeriesPresenterTrait;

    protected function startup(): void {
        parent::startup();
        $this->seriesTraitStartup();
    }

    protected function getNavRoots(): array {
        return ['Org.Dashboard.default'];
    }

    protected function beforeRender(): void {
        $contest = $this->getSelectedContest();
        if (isset($contest) && $contest) {
            $this->getPageStyleContainer()->styleId = $contest->getContestSymbol();
            $this->getPageStyleContainer()->setNavBarClassName('navbar-dark bg-' . $contest->getContestSymbol());
        }
        parent::beforeRender();
    }

    protected function getDefaultSubTitle(): ?string {
        return sprintf(_('%d. year, %s. series'), $this->getSelectedContestYear()->year, $this->getSelectedSeries());
    }

    /**
     * @param Resource|string|null $resource
     * @param string|null $privilege
     * @return bool
     */
    protected function isAnyContestAuthorized($resource, ?string $privilege): bool {
        return $this->contestAuthorizator->isAllowedForAnyContest($resource, $privilege);
    }

    protected function getRole(): string {
        return 'org';
    }
}
