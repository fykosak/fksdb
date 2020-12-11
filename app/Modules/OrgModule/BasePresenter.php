<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Modules\Core\PresenterTraits\ISeriesPresenter;
use FKSDB\Modules\Core\PresenterTraits\SeriesPresenterTrait;
use FKSDB\Model\ORM\Models\ModelLogin;
use FKSDB\Model\UI\PageTitle;
use Nette\Security\IResource;

abstract class BasePresenter extends AuthenticatedPresenter implements ISeriesPresenter {
    use SeriesPresenterTrait;

    protected function startup(): void {
        $this->seriesTraitStartup();
        /*  @var ModelLogin $login
         * $login = $this->getUser()->getIdentity();
         * if (!$login || !$login->getPerson() || !$login->getPerson()->getActiveOrgsAsQuery($this->yearCalculator, $this->getSelectedContest())->count()) {
         * throw new ForbiddenRequestException();
         * }*/
        parent::startup();
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

    protected function setPageTitle(PageTitle $pageTitle): void {
        $pageTitle->subTitle = sprintf(_('%d. year, %s. series'), $this->getSelectedYear(), $this->getSelectedSeries()) . ' ' . $pageTitle->subTitle;
        parent::setPageTitle($pageTitle);
    }

    /**
     * @param IResource|string|null $resource
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
