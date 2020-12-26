<?php

namespace FKSDB\Modules\OrgModule\Warehouse;

use FKSDB\Modules\Core\AuthenticatedPresenter;
use FKSDB\Modules\Core\PresenterTraits\ContestPresenterTrait;
use Nette\Security\IResource;

/**
 * Class BasePresenter
 * @author Michal Červeňák <miso@fykos.cz>
 */
abstract class BasePresenter extends AuthenticatedPresenter {

    use ContestPresenterTrait;

    protected function startup(): void {
        $this->contestTraitStartup();
        parent::startup();
    }

    /**
     * @param IResource|string|null $resource
     * @param string|null $privilege
     * @return bool
     */
    protected function isAllowed($resource, ?string $privilege): bool {
        return $this->contestAuthorizator->isAllowed($resource, $privilege, $this->getSelectedContest());
    }

    protected function getRole(): string {
        return 'org';
    }

    protected function beforeRender(): void {
        $contest = $this->getSelectedContest();
        if (isset($contest) && $contest) {
            $this->getPageStyleContainer()->styleId = $contest->getContestSymbol();
            $this->getPageStyleContainer()->setNavBarClassName('navbar-dark bg-' . $contest->getContestSymbol());
        }
        parent::beforeRender();
    }
}
