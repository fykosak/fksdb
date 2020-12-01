<?php

namespace FKSDB\Modules\OrgModule;

use FKSDB\Components\Controls\Choosers\YearChooser;
use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Modules\Core\PresenterTraits\YearPresenterTrait;
use FKSDB\UI\PageTitle;

/**
 * Presenter keeps chosen contest, year and language in session.
 *
 * @author Michal Koutný <michal@fykos.cz>
 */
abstract class BasePresenter extends AuthenticatedPresenter {
    use YearPresenterTrait;

    protected function startup(): void {
        $this->yearTraitStartup(YearChooser::ROLE_ORG);
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
        $pageTitle->subTitle = sprintf(_('%d. year'), $this->year) . ' ' . $pageTitle->subTitle;
        parent::setPageTitle($pageTitle);
    }
}
